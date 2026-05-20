<?php

use App\Models\Loan;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Modules\User\App\Models\User;

describe('fillable', function () {
    test('name', function () {
        expect(new User(['name' => 'Alice']))->name->toBe('Alice');
    });

    test('email', function () {
        expect(new User(['email' => 'alice@example.com']))->email->toBe('alice@example.com');
    });

    test('password is accepted and hashed', function () {
        $user = new User(['password' => 'secret']);
        expect($user->password)->not->toBeNull();
        expect(Hash::check('secret', $user->password))->toBeTrue();
    });

    test('role', function () {
        expect(new User(['role' => 'admin']))->role->toBe('admin');
    });

    test('non-fillable remember_token is ignored', function () {
        expect(new User(['remember_token' => 'token123']))->remember_token->toBeNull();
    });
});

describe('hidden attributes', function () {
    beforeEach(function () {
        $this->user = User::factory()->make();
        $this->array = $this->user->toArray();
    });

    test('password is not serialized', function () {
        expect($this->array)->not->toHaveKey('password');
    });

    test('remember_token is not serialized', function () {
        expect($this->array)->not->toHaveKey('remember_token');
    });

    test('name and email are serialized', function () {
        expect($this->array)->toHaveKey('name')->toHaveKey('email');
    });
});

describe('casts', function () {
    beforeEach(function () {
        $this->user = User::factory()->create(['password' => 'plain_password']);
    });

    test('email_verified_at is a Carbon instance', function () {
        expect($this->user->email_verified_at)->toBeInstanceOf(Carbon::class);
    });

    test('password is hashed on save', function () {
        expect(Hash::check('plain_password', $this->user->password))->toBeTrue();
    });

    test('stored password is not plain text', function () {
        expect($this->user->password)->not->toBe('plain_password');
    });
});

describe('role', function () {
    test('defaults to user from the database default', function () {
        expect(User::factory()->create()->fresh()->role)->toBe('user');
    });

    test('can be set to admin', function () {
        expect(User::factory()->create(['role' => 'admin'])->role)->toBe('admin');
    });

    test('persists after saving', function () {
        expect(User::factory()->create(['role' => 'admin'])->fresh()->role)->toBe('admin');
    });
});

describe('loans relationship', function () {
    beforeEach(function () {
        $this->user = new User();
    });

    test('loans() returns a HasMany instance', function () {
        expect($this->user->loans())->toBeInstanceOf(HasMany::class);
    });

    test('loans() is related to the Loan model', function () {
        expect($this->user->loans()->getRelated()::class)->toBe(Loan::class);
    });

    test('loans collection is empty when user has no loans', function () {
        expect(User::factory()->create()->loans)->toHaveCount(0);
    });
});

describe('factory', function () {
    test('creates users with unique emails', function () {
        [$a, $b] = User::factory()->count(2)->create();
        expect($a->email)->not->toBe($b->email);
    });

    test('sets email_verified_at by default', function () {
        expect(User::factory()->create()->email_verified_at)->not->toBeNull();
    });

    test('unverified state sets email_verified_at to null', function () {
        expect(User::factory()->unverified()->create()->email_verified_at)->toBeNull();
    });

    test('allows attribute overrides', function () {
        $user = User::factory()->create(['name' => 'Custom Name', 'email' => 'custom@example.com']);
        expect($user->name)->toBe('Custom Name');
        expect($user->email)->toBe('custom@example.com');
    });

    test('make does not persist to the database', function () {
        User::factory()->make();
        expect(User::count())->toBe(0);
    });

    test('create persists to the database', function () {
        User::factory()->create();
        expect(User::count())->toBe(1);
    });
});

describe('database constraints', function () {
    test('email must be unique', function () {
        User::factory()->create(['email' => 'dup@example.com']);
        User::factory()->create(['email' => 'dup@example.com']);
    })->throws(UniqueConstraintViolationException::class);
});
