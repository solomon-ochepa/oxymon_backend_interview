<?php

use Modules\Loan\App\Http\Requests\UpdateLoanRequest;

describe('UpdateLoanRequest', function () {
    beforeEach(function () {
        $this->request = new UpdateLoanRequest;
        $this->rules = $this->request->rules();
    });

    describe('authorization', function () {
        test('authorize() returns true', function () {
            expect($this->request->authorize())->toBeTrue();
        });
    });

    describe('rules structure', function () {
        test('has user_id rule', fn () => expect($this->rules)->toHaveKey('user_id'));
        test('has amount rule', fn () => expect($this->rules)->toHaveKey('amount'));
        test('has interest rule', fn () => expect($this->rules)->toHaveKey('interest'));
        test('has term rule', fn () => expect($this->rules)->toHaveKey('term'));
        test('has status rule', fn () => expect($this->rules)->toHaveKey('status'));

        test('does not have borrower_name rule', fn () => expect($this->rules)->not->toHaveKey('borrower_name'));
        test('does not have borrower_email rule', fn () => expect($this->rules)->not->toHaveKey('borrower_email'));

        test('has exactly 5 rules', fn () => expect(count($this->rules))->toBe(5));
    });

    describe('user_id rules', function () {
        test('user_id is always required', fn () => expect($this->rules['user_id'])->toContain('required'));
        test('user_id must exist in users table', function () {
            expect(collect($this->rules['user_id'])->map(fn ($r) => (string) $r)->join(','))
                ->toContain('exists:users,id');
        });
        test('user_id does not have sometimes', fn () => expect($this->rules['user_id'])->not->toContain('sometimes'));
    });

    describe('optional fields use sometimes', function () {
        test('amount is sometimes', fn () => expect($this->rules['amount'])->toContain('sometimes'));
        test('interest is sometimes', fn () => expect($this->rules['interest'])->toContain('sometimes'));
        test('term is sometimes', fn () => expect($this->rules['term'])->toContain('sometimes'));
        test('status is sometimes', fn () => expect($this->rules['status'])->toContain('sometimes'));
    });

    describe('amount rules', function () {
        test('amount has min:1', fn () => expect($this->rules['amount'])->toContain('min:1'));
        test('amount has max:9999999999', fn () => expect($this->rules['amount'])->toContain('max:9999999999'));
        test('amount is numeric', fn () => expect($this->rules['amount'])->toContain('numeric'));
    });

    describe('interest rules', function () {
        test('interest has min:0', fn () => expect($this->rules['interest'])->toContain('min:0'));
        test('interest has max:100', fn () => expect($this->rules['interest'])->toContain('max:100'));
    });

    describe('term rules', function () {
        test('term is integer', fn () => expect($this->rules['term'])->toContain('integer'));
        test('term has min:1', fn () => expect($this->rules['term'])->toContain('min:1'));
        test('term has max:600', fn () => expect($this->rules['term'])->toContain('max:600'));
    });
});
