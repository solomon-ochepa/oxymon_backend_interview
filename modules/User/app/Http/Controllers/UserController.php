<?php

namespace Modules\User\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    /**
     * Return the authenticated user.
     */
    #[OA\Get(
        path: '/api/me',
        summary: 'Get the authenticated user',
        tags: ['Auth', 'User'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'The authenticated user'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user()->only(['id', 'name', 'email']));
    }
}
