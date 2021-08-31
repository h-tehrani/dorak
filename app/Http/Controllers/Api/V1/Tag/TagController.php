<?php

namespace App\Http\Controllers\Api\V1\Tag;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Tag\StoreTagRequest;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use App\Repository\Eloquent\TagRepository;
use App\Repository\TagRepositoryInterface;
use App\Traits\ApiResponse;
use App\Traits\StoreImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use \Illuminate\Http\Client\Response as HttpResponse;

class TagController extends Controller
{
    #Response & StoreImage Trait
    use ApiResponse, StoreImage;

    private TagRepositoryInterface $tagRepository;
    private int $tries = 0;

    #Repository injection
    public function __construct(TagRepositoryInterface $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    public function index(): JsonResponse
    {
        return $this->success($this->tagRepository->index(), 'success.tag.index');
    }

    public function store(StoreTagRequest $request): JsonResponse
    {
        $user = Auth()->user();
        $nickname = $user['nickname'];
        $validatedData = $request->validated();

        $path = $this->save($validatedData['file'], $nickname);

        $response = $this->sendRequest($path);

        #network or curl error
        if (!$response) {
            return $this->error('errors.tag.curl.unknown');
        }

        $tag = json_decode($response, true)['result']['tags'][0];

        #discard original image
        File::delete($path);

        #make thumbnail
        $path = $this->save($validatedData['file'], $nickname, 160, 160);

        #relative path
        $path = strstr($path, 'storage');

        $data = [
            'name' => Arr::first($tag['tag']),
            'confidence' => (integer)$tag['confidence'],
            'path' => $path,
            'user_id' => $user['id'],
        ];

        $this->tagRepository->create($data);

        return $this->success(['describe' => $data['name']], 'success.tag.create', 201);
    }

    public function sendRequest($path): HttpResponse|bool
    {
        try {
            $response = Http::withHeaders(['Authorization' => config('app.imagga_token')])
                ->timeout(60)
                ->attach('image', file_get_contents($path), 'image.jpg')
                ->post("https://api.imagga.com/v2/tags");

        } catch (\Exception $exception) {
            if ($exception->getCode() === 0 && $this->tries < 3) {
                $this->tries++;
                $response = $this->sendRequest($path);
            } else {
                return false;
            }
        }
        return $response;
    }

    public function remain(): JsonResponse
    {
        $used = $this->tagRepository->remain();
        $data = [
            'used' => $used,
            'remain' => 25 - $used
        ];
        return $this->success($data, 'success.tag.status');
    }

    public function history(): JsonResponse
    {
        $tags = TagResource::collection($this->tagRepository->history());

        return $this->success($tags, 'success.tag.history');
    }

    public function top(): JsonResponse
    {
        $topTags = $this->tagRepository->top();

        return $this->success($topTags, 'success.tag.top');
    }
}
