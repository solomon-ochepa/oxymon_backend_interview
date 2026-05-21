<?php

use Illuminate\Validation\Rules\Password;
use Modules\Auth\App\Http\Requests\RegisterRequest;

describe('RegisterRequest', function () {
    beforeEach(function () {
        $this->request = new RegisterRequest;
        $this->rules = $this->request->rules();
    });

    describe('authorization', function () {
        test('authorize() returns true', function () {
            expect($this->request->authorize())->toBeTrue();
        });
    });

    describe('rules structure', function () {
        test('has name rule', fn () => expect($this->rules)->toHaveKey('name'));
        test('has email rule', fn () => expect($this->rules)->toHaveKey('email'));
        test('has password rule', fn () => expect($this->rules)->toHaveKey('password'));

        test('has exactly 3 rules', fn () => expect(count($this->rules))->toBe(3));

        test('does not have password_confirmation rule', fn () => expect($this->rules)->not->toHaveKey('password_confirmation'));
        test('does not have role rule', fn () => expect($this->rules)->not->toHaveKey('role'));
    });

    describe('name rules', function () {
        test('name is required', fn () => expect($this->rules['name'])->toContain('required'));
        test('name is string', fn () => expect($this->rules['name'])->toContain('string'));
        test('name has max:255', fn () => expect($this->rules['name'])->toContain('max:255'));
    });

    describe('email rules', function () {
        test('email is required', fn () => expect($this->rules['email'])->toContain('required'));
        test('email must be valid email format', fn () => expect($this->rules['email'])->toContain('email'));
        test('email has max:255', fn () => expect($this->rules['email'])->toContain('max:255'));
        test('email must be unique in users table', function () {
            $stringRules = collect($this->rules['email'])->map(fn ($r) => (string) $r)->join(',');
            expect($stringRules)->toContain('unique:users,email');
        });
    });

    describe('password rules', function () {
        test('password is required', fn () => expect($this->rules['password'])->toContain('required'));
        test('password requires confirmation', fn () => expect($this->rules['password'])->toContain('confirmed'));
        test('password uses Password defaults rule', function () {
            $hasPasswordRule = collect($this->rules['password'])->contains(
                fn ($rule) => $rule instanceof Password
            );
            expect($hasPasswordRule)->toBeTrue();
        });
    });
});
