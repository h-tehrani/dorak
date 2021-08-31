<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Repository\UserRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    #Response Trait
    use ApiResponse;

    private UserRepositoryInterface $userRepository;

    #Repository injection
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $user = $this->userRepository->create($validatedData);

        return $this->success($user, 'success.user.create', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        if (Auth::attempt($validatedData)) {

            $token = auth()->user()->createToken('API Token')->plainTextToken;

            return $this->success(['token' => $token], 'success.user.loggedIn');
        }

        return $this->error('errors.user.wrongCredentials', 401);

    }

    public function logout(): JsonResponse
    {
        if (auth()->user()->tokens()->delete()) {
            return $this->success(null, 'success.user.loggedOut');
        }

        return $this->error('errors.user.loggedOut');
    }

}
