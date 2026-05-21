<?php

use Modules\Auth\App\Http\Requests\LoginRequest;

describe('LoginRequest', function () {
    beforeEach(function () {
        $this->request = new LoginRequest;
        $this->rules = $this->request->rules();
    });

    describe('authorization', function () {
        test('authorize() returns true', function () {
            expect($this->request->authorize())->toBeTrue();
        });
    });

    describe('rules structure', function () {
        test('has email rule', fn () => expect($this->rules)->toHaveKey('email'));
        test('has password rule', fn () => expect($this->rules)->toHaveKey('password'));

        test('has exactly 2 rules', fn () => expect(count($this->rules))->toBe(2));

        test('does not have name rule', fn () => expect($this->rules)->not->toHaveKey('name'));
        test('does not have remember_me rule', fn () => expect($this->rules)->not->toHaveKey('remember_me'));
    });

    describe('email rules', function () {
        test('email is required', fn () => expect($this->rules['email'])->toContain('required'));
        test('email must be valid email format', fn () => expect($this->rules['email'])->toContain('email'));
    });

    describe('password rules', function () {
        test('password is required', fn () => expect($this->rules['password'])->toContain('required'));
        test('password is string', fn () => expect($this->rules['password'])->toContain('string'));

        test('password does not have confirmed rule', fn () => expect($this->rules['password'])->not->toContain('confirmed'));
        test('password does not have min length rule', function () {
            $stringRules = collect($this->rules['password'])->map(fn ($r) => (string) $r)->join(',');
            expect($stringRules)->not->toContain('min:');
        });
    });
});
