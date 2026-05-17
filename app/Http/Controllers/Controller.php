<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Loan App API',
    description: 'A simple loan management API built with Laravel.',
)]
#[OA\Server(
    url: 'http://127.0.0.1:8000',
    description: 'Local development server',
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    description: 'Sanctum personal access token. Send as: Authorization: Bearer {token}',
)]
#[OA\Schema(
    schema: 'Loan',
    title: 'Loan',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 1),
        new OA\Property(property: 'borrower_name', type: 'string', example: 'Ada Lovelace'),
        new OA\Property(property: 'borrower_email', type: 'string', format: 'email', example: 'ada@example.com'),
        new OA\Property(property: 'amount', type: 'string', example: '5000.00'),
        new OA\Property(property: 'interest_rate', type: 'string', example: '12.50'),
        new OA\Property(property: 'term_months', type: 'integer', example: 24),
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'approved', 'active', 'paid', 'rejected'], example: 'pending'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'LoanInput',
    title: 'Loan input',
    required: ['borrower_name', 'borrower_email', 'amount', 'interest_rate', 'term_months'],
    properties: [
        new OA\Property(property: 'borrower_name', type: 'string', example: 'Ada Lovelace'),
        new OA\Property(property: 'borrower_email', type: 'string', format: 'email', example: 'ada@example.com'),
        new OA\Property(property: 'amount', type: 'number', format: 'float', example: 5000),
        new OA\Property(property: 'interest_rate', type: 'number', format: 'float', example: 12.5),
        new OA\Property(property: 'term_months', type: 'integer', example: 24),
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'approved', 'active', 'paid', 'rejected'], example: 'pending'),
    ],
    type: 'object',
)]
abstract class Controller
{
    //
}
