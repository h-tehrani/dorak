<?php

namespace App\Jobs;

use App\Events\Message;
use App\Events\Update;
use App\Models\Tag;
use App\Repository\TagRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SendRequestToImagga implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $nth;
    private string $path, $hash;
    private int $id;
    private TagRepositoryInterface $tagRepository;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $nth, string $path, string $hash, int $id, TagRepositoryInterface $tagRepository)
    {
        $this->nth = $nth;
        $this->path = $path;
        $this->hash = $hash;
        $this->id = $id;
        $this->tagRepository = $tagRepository;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Cache::forget($this->id);
        Cache::add($this->id, $this->nth);
        $response = Http::withHeaders(['Authorization' => config('app.imagga_token')])
            ->timeout(60)
            ->attach('image', file_get_contents($this->path), 'image.jpg')
            ->post("https://api.imagga.com/v2/tags");
        if (isset(json_decode($response, true)['result']['tags'][0])) {
            $tag = json_decode($response, true)['result']['tags'][0];

            $this->path = strstr($this->path, 'storage');

            $data = [
                'name' => Arr::first($tag['tag']),
                'confidence' => (integer)$tag['confidence'],
                'path' => $this->path,
                'hash' => $this->hash,
                'user_id' => $this->id,
            ];

            broadcast(new Update($this->nth));

            $this->tagRepository->create($data);

            if (isset($this->nth['all']) && $this->nth['all'] === $this->nth['current']) {
                $data = [
                    'title' => 'Response is ready!',
                    'body' => 'please check your history.'
                ];
                Cache::forget($this->id);
                broadcast(new Message($data, $this->id));
            }
        } else {
            SendRequestToImagga::dispatch($this->nth, $this->path, $this->hash, $this->id, $this->tagRepository)->delay(now()->addSeconds(5));
        }
    }
}
