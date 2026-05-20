<?php

namespace Modules\Loan\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Loan\App\Http\Requests\StoreLoanRequest;
use Modules\Loan\App\Http\Requests\UpdateLoanRequest;
use Modules\Loan\App\Http\Resources\LoanResource;
use Modules\Loan\App\Models\Loan;
use OpenApi\Attributes as OA;

class LoanController extends Controller
{
    /**
     * Retrieve all loans (most recent first, paginated).
     */
    #[OA\Get(
        path: '/api/loans',
        summary: 'Retrieve all loans',
        tags: ['Loans'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of loans',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Loan'),
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function index(): AnonymousResourceCollection
    {
        $loans = Loan::query()->latest()->paginate(15);

        return LoanResource::collection($loans);
    }

    /**
     * Retrieve all loans belonging to the authenticated user.
     */
    #[OA\Get(
        path: '/api/loans/me',
        summary: "Retrieve the authenticated user's loans",
        tags: ['Loans'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Paginated list of the current user's loans",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Loan'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function myLoans(Request $request): AnonymousResourceCollection
    {
        $loans = $request->user()
            ->loans()
            ->latest()
            ->paginate(15);

        return LoanResource::collection($loans);
    }

    /**
     * Create a loan.
     */
    #[OA\Post(
        path: '/api/loans',
        summary: 'Create a loan',
        tags: ['Loans'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/LoanInput'),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Loan created',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Loan')],
                ),
            ),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function store(StoreLoanRequest $request): JsonResponse
    {
        $loan = $request->user()->loans()->create($request->validated());
        $loan->refresh(); // reflect DB-side defaults (e.g. status) in the response

        return LoanResource::make($loan)
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Retrieve a single loan.
     */
    #[OA\Get(
        path: '/api/loans/{loan}',
        summary: 'Retrieve a single loan',
        tags: ['Loans'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'loan',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The loan',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Loan')],
                ),
            ),
            new OA\Response(response: 404, description: 'Loan not found'),
        ],
    )]
    public function show(Loan $loan): LoanResource
    {
        return LoanResource::make($loan);
    }

    /**
     * Update a loan.
     */
    #[OA\Put(
        path: '/api/loans/{loan}',
        summary: 'Update a loan',
        tags: ['Loans'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'loan',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/LoanInput'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Loan updated',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Loan')],
                ),
            ),
            new OA\Response(response: 404, description: 'Loan not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function update(UpdateLoanRequest $request, Loan $loan): LoanResource
    {
        $loan->update($request->validated());

        return LoanResource::make($loan);
    }

    /**
     * Delete a loan.
     */
    #[OA\Delete(
        path: '/api/loans/{loan}',
        summary: 'Delete a loan',
        tags: ['Loans'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'loan',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Loan deleted'),
            new OA\Response(response: 404, description: 'Loan not found'),
        ],
    )]
    public function destroy(Loan $loan): JsonResponse
    {
        $loan->delete();

        return response()->json(null, 204);
    }
}
