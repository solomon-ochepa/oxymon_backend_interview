<?php

use Modules\User\App\Models\User;
use Modules\User\Database\Factories\UserFactory;

describe('UserFactory', function () {
    beforeEach(function () {
        $this->factory = User::factory();
        $this->made = User::factory()->make();
    });

    test('User::factory() returns a UserFactory instance', function () {
        expect($this->factory)->toBeInstanceOf(UserFactory::class);
    });

    describe('definition', function () {
        test('provides a non-empty name', function () {
            expect($this->made->name)->not->toBeEmpty();
        });

        test('provides a non-empty email', function () {
            expect($this->made->email)->not->toBeEmpty();
        });

        test('provides a non-empty hashed password', function () {
            expect($this->made->password)->not->toBeEmpty();
        });

        test('sets email_verified_at by default', function () {
            expect($this->made->email_verified_at)->not->toBeNull();
        });
    });

    describe('unverified state', function () {
        beforeEach(function () {
            $this->unverified = User::factory()->unverified()->make();
        });

        test('sets email_verified_at to null', function () {
            expect($this->unverified->email_verified_at)->toBeNull();
        });
    });

    describe('batch creation', function () {
        test('can create multiple users', function () {
            User::factory()->count(5)->create();
            expect(User::count())->toBe(5);
        });

        test('static password cache is shared across instances', function () {
            $ref = new ReflectionProperty(UserFactory::class, 'password');
            $ref->setValue(null, null);

            [$a, $b] = User::factory()->count(2)->create();
            expect($a->password)->toBe($b->password);
        });
    });

    describe('state overrides', function () {
        test('state() overrides individual attributes', function () {
            $user = User::factory()->state(['name' => 'Override Name'])->make();
            expect($user->name)->toBe('Override Name');
        });
    });
});
