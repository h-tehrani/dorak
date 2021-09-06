<?php

namespace App\Console\Commands;

use App\Events\Message;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Console\Command;

class Notification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify Users';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = User::query()->where('last_online_time', '<', Now()->subHours(3))->get();
        foreach ($users as $user) {
            $tag = Tag::query()
                ->where('user_id', $user['id'])
                ->select('name')
                ->groupBy('name')
                ->orderByRaw('COUNT(*) DESC')
                ->first();
            if ($tag) {
                $data = [
                    'title' => 'Dear ' . $user['nickname'],
                    'body' => 'Your most used tag is ' . $tag['name'] . '. Do you want to upload another ' . $tag['name'] . ' ?'
                ];
            } else {
                $data = [
                    'title' => 'Dear ' . $user['nickname'],
                    'body' => 'You have not used our app yet. try to upload a picture.'
                ];
            }
            broadcast(new Message($data, $user['id']));
        }
    }
}
