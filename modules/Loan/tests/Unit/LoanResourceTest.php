<?php

use Illuminate\Http\Request;
use Modules\Loan\App\Http\Resources\LoanResource;
use Modules\Loan\App\Models\Loan;
use Modules\User\App\Models\User;

describe('LoanResource', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->loan = Loan::factory()->forUser($this->user)->create([
            'amount' => '2500.00',
            'interest' => '8.50',
            'term' => 12,
            'status' => 'approved',
        ]);
        $this->array = LoanResource::make($this->loan)
            ->toArray(Request::create('/api/loans/1'));
    });

    describe('structure', function () {
        test('contains id', fn () => expect($this->array)->toHaveKey('id'));
        test('contains user_id', fn () => expect($this->array)->toHaveKey('user_id'));
        test('contains amount', fn () => expect($this->array)->toHaveKey('amount'));
        test('contains interest', fn () => expect($this->array)->toHaveKey('interest'));
        test('contains term', fn () => expect($this->array)->toHaveKey('term'));
        test('contains status', fn () => expect($this->array)->toHaveKey('status'));
        test('contains created_at', fn () => expect($this->array)->toHaveKey('created_at'));
        test('contains updated_at', fn () => expect($this->array)->toHaveKey('updated_at'));

        test('does not contain borrower_name', fn () => expect($this->array)->not->toHaveKey('borrower_name'));
        test('does not contain borrower_email', fn () => expect($this->array)->not->toHaveKey('borrower_email'));

        test('contains exactly 8 keys', fn () => expect(count($this->array))->toBe(8));
    });

    describe('values', function () {
        test('id matches model id', fn () => expect($this->array['id'])->toBe($this->loan->id));
        test('user_id matches model user_id', fn () => expect($this->array['user_id'])->toBe($this->user->id));
        test('amount matches', fn () => expect($this->array['amount'])->toBe('2500.00'));
        test('interest matches', fn () => expect($this->array['interest'])->toBe('8.50'));
        test('term matches', fn () => expect($this->array['term'])->toBe(12));
        test('status matches', fn () => expect($this->array['status'])->toBe('approved'));
    });
});
