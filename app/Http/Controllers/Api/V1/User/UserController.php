<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Filters\Filter;
use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Repository\UserRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;

class UserController extends Controller
{
    use ApiResponse;

    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index(): JsonResponse
    {
        $users = $this->userRepository->all();

        return $this->success($users, 'success.users.index');
    }

    public function getUsers(Request $request): JsonResponse
    {
        # pipeline for most extendable filter
        $users=app(Pipeline::class)->send(Tag::query())->through([
            Filter::class,
        ])->thenReturn();

        return $this->success($users, 'success.tags.filter');
    }
}
