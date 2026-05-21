<?php

namespace Modules\Auth\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Auth\App\Http\Requests\LoginRequest;
use Modules\Auth\App\Http\Requests\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\User\App\Models\User;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    /**
     * Register a new user and issue an API token.
     */
    #[OA\Post(
        path: '/api/register',
        summary: 'Register a new user',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Ada Lovelace'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ada@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'Password123!'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'Password123!'),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 201, description: 'User registered, token issued'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => $user->only(['id', 'name', 'email']),
            'token' => $token,
        ], 201);
    }

    /**
     * Authenticate a user and issue an API token.
     */
    #[OA\Post(
        path: '/api/login',
        summary: 'Log in',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ada@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'Password123!'),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 200, description: 'Authenticated, token issued'),
            new OA\Response(response: 422, description: 'Invalid credentials'),
        ],
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => $user->only(['id', 'name', 'email']),
            'token' => $token,
        ]);
    }

    /**
     * Revoke the current access token (log out).
     */
    #[OA\Post(
        path: '/api/logout',
        summary: 'Log out (revoke current token)',
        tags: ['Auth'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 204, description: 'Token revoked'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(null, 204);
    }
}
