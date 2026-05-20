<?php

use Modules\User\App\Models\User;
use Modules\User\Database\Seeders\UserDatabaseSeeder;
use Modules\User\Database\Seeders\UserSeeder;

describe('UserSeeder', function () {
    describe('when the table is empty', function () {
        beforeEach(function () {
            $this->seed(UserSeeder::class);
        });

        test('creates exactly one user', function () {
            expect(User::count())->toBe(1);
        });

        test('creates the test user with the correct email', function () {
            expect(User::where('email', 'test@example.com')->exists())->toBeTrue();
        });

        test('creates the test user with the correct name', function () {
            expect(User::where('name', 'Test User')->exists())->toBeTrue();
        });
    });

    describe('when users already exist', function () {
        test('does not create a user when one already exists', function () {
            User::factory()->create();
            $this->seed(UserSeeder::class);
            expect(User::count())->toBe(1);
        });

        test('does not create a user when multiple already exist', function () {
            User::factory()->count(3)->create();
            $this->seed(UserSeeder::class);
            expect(User::count())->toBe(3);
        });
    });

    describe('idempotency', function () {
        test('running twice still yields one user', function () {
            $this->seed(UserSeeder::class);
            $this->seed(UserSeeder::class);
            expect(User::count())->toBe(1);
        });
    });
});

describe('UserDatabaseSeeder', function () {
    beforeEach(function () {
        $this->seed(UserDatabaseSeeder::class);
    });

    test('creates exactly one user', function () {
        expect(User::count())->toBe(1);
    });

    test('creates the correct seed user', function () {
        expect(User::where(['name' => 'Test User', 'email' => 'test@example.com'])->exists())->toBeTrue();
    });

    test('is idempotent when run twice', function () {
        $this->seed(UserDatabaseSeeder::class);
        expect(User::count())->toBe(1);
    });
});
