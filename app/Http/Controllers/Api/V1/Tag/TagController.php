<?php

namespace App\Http\Controllers\Api\V1\Tag;

use App\Events\Message;
use App\Events\Update;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Tag\StoreMultipleTagRequest;
use App\Http\Requests\Api\V1\Tag\StoreTagRequest;
use App\Http\Resources\TagResource;
use App\Jobs\SendRequestToImagga;
use App\Jobs\TagMultipleImages;
use App\Models\Tag;
use App\Models\User;
use App\Repository\Eloquent\TagRepository;
use App\Repository\TagRepositoryInterface;
use App\Traits\ApiResponse;
use App\Traits\StoreImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
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

        $hash = hash_file('sha256', $validatedData['file']);

        if ($data = Tag::query()->where('hash', $hash)->first()) {

            return $this->success(['describe' => $data['name']], 'success.tag.create', 201);

        }

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
            'hash' => $hash,
            'user_id' => $user['id'],
        ];

        $this->tagRepository->create($data);

        return $this->success(['describe' => $data['name']], 'success.tag.create', 201);
    }

    public function storeMultiple(StoreMultipleTagRequest $request): JsonResponse
    {
        $user = Auth()->user();
        $validatedData = $request->validated()['files'];
        $paths = [];
        $hash = [];

        foreach ($validatedData as $key => $file) {
            $paths[] = $this->save($file, $user['nickname']);
            $hash[] = hash_file('sha256', $file);
            if (!count(Tag::query()->where('hash', $hash[$key])->get())) {
                $nth['all'] = count($validatedData);
                $nth['current'] = $key + 1;
                $delay = $key * 2;
                SendRequestToImagga::dispatch($nth, $paths[$key], $hash[$key], $user['id'], $this->tagRepository)->delay(now()->addSeconds($delay));
            }
        }
        return $this->success(null, 'success.tag.create', 201);
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

    public function lastHistory(): JsonResponse
    {
        $tags = TagResource::collection($this->tagRepository->history()->take(5));

        return $this->success($tags, 'success.tag.history');
    }

    public function top(): JsonResponse
    {
        $topTags = $this->tagRepository->top();

        return $this->success($topTags, 'success.tag.top');
    }

    public function clearHistory(): JsonResponse
    {
        $this->tagRepository->clear();
        return $this->success(null, 'success.tag.clear');
    }

    public function status(): JsonResponse
    {
        return $this->success(Cache::get(Auth()->id()), 'success.status.sent');
    }

    public function clearStatus(): JsonResponse
    {
        Cache::forget(Auth()->id());
        return $this->success(null, 'success.status.clear');
    }
}
