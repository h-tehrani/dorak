<?php

namespace App\Filters;


use App\Models\Tag;
use App\Models\User;
use App\Repository\UserRepositoryInterface;
use Illuminate\Support\Str;

class Filter
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function handle($request, \closure $next)
    {
        if (!\request()->has('by')) {

            #return all users
            return $this->userRepository->all();
        }

        $builder = $next($request);

        $users = [];

        switch (Request()->get('by')) {
            case 'activity':
                $tags = $builder
                    ->select('user_id')
                    ->groupBy('user_id')
                    ->orderByRaw('COUNT(*) DESC')
                    ->take(3)
                    ->with('user')
                    ->get()
                    ->toArray();

                foreach ($tags as $tag) {
                    $users[] = $tag['user'];
                }
                return $users;

            case 'tag':

                #get all tags
                $tags = $builder->select('name', 'user_id')->with('user')->get()->toArray();

                # $temp determine count of users per tags
                $temp = [];
                foreach ($tags as $tag) {
                    $name = $tag['name'];
                    $userNickname = $tag['user']['nickname'];
                    if (isset($temp[$name][$userNickname])) {
                        $temp[$name][$userNickname] = $temp[$name][$userNickname] + 1;
                    } else {
                        $temp[$name][$userNickname] = 1;
                    }
                }

                # final Data - top user in each tag
                $data = [];
                foreach ($temp as $key => $item) {
                    $data[$key] = array_keys($item, max($item))[0];
                }
                return $data;
        }
    }
}
