<?php

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Loan\App\Models\Loan;
use Modules\Loan\Database\Factories\LoanFactory;
use Modules\User\App\Models\User;

describe('fillable', function () {
    beforeEach(function () {
        $this->loan = new Loan([
            'user_id' => 1,
            'amount' => '5000.00',
            'interest' => '12.50',
            'term' => 24,
            'status' => 'pending',
        ]);
    });

    test('user_id', fn () => expect($this->loan->user_id)->toBe(1));
    test('amount', fn () => expect($this->loan->amount)->toBe('5000.00'));
    test('interest', fn () => expect($this->loan->interest)->toBe('12.50'));
    test('term', fn () => expect($this->loan->term)->toBe(24));
    test('status', fn () => expect($this->loan->status)->toBe('pending'));

    test('borrower_name is not fillable', function () {
        $loan = new Loan(['borrower_name' => 'Ignored']);
        expect($loan->borrower_name)->toBeNull();
    });

    test('borrower_email is not fillable', function () {
        $loan = new Loan(['borrower_email' => 'ignored@example.com']);
        expect($loan->borrower_email)->toBeNull();
    });
});

describe('casts', function () {
    beforeEach(function () {
        $this->loan = Loan::factory()->create([
            'amount' => 1234.5,
            'interest' => 9.9,
            'term' => 36,
        ]);
    });

    test('amount is cast to decimal string with two decimal places', function () {
        expect($this->loan->amount)->toBe('1234.50');
    });

    test('interest is cast to decimal string with two decimal places', function () {
        expect($this->loan->interest)->toBe('9.90');
    });

    test('term is cast to integer', function () {
        expect($this->loan->term)->toBeInt()->toBe(36);
    });
});

describe('status', function () {
    test('default status is pending (from DB default)', function () {
        expect(Loan::factory()->create()->fresh()->status)->toBe('pending');
    });

    test('status can be set to approved', fn () => expect(Loan::factory()->approved()->create()->status)->toBe('approved'));
    test('status can be set to active', fn () => expect(Loan::factory()->active()->create()->status)->toBe('active'));
    test('status can be set to paid', fn () => expect(Loan::factory()->paid()->create()->status)->toBe('paid'));
    test('status can be set to rejected', fn () => expect(Loan::factory()->rejected()->create()->status)->toBe('rejected'));
});

describe('factory', function () {
    test('Loan::factory() returns a LoanFactory instance', function () {
        expect(Loan::factory())->toBeInstanceOf(LoanFactory::class);
    });

    test('factory create persists to the database', function () {
        Loan::factory()->create();
        expect(Loan::count())->toBe(1);
    });

    test('factory make does not persist', function () {
        Loan::factory()->make();
        expect(Loan::count())->toBe(0);
    });

    test('forUser state assigns loan to the given user', function () {
        $user = User::factory()->create();
        expect(Loan::factory()->forUser($user)->create()->user_id)->toBe($user->id);
    });
});

describe('user relationship', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->loan = Loan::factory()->forUser($this->user)->create();
    });

    test('user() returns a BelongsTo instance', function () {
        expect($this->loan->user())->toBeInstanceOf(BelongsTo::class);
    });

    test('user() is related to the User model', function () {
        expect($this->loan->user()->getRelated()::class)->toBe(User::class);
    });

    test('user relationship resolves to the owning user', function () {
        expect($this->loan->user->id)->toBe($this->user->id);
    });

    test('user name is accessible through relationship', function () {
        expect($this->loan->user->name)->toBe($this->user->name);
    });

    test('user email is accessible through relationship', function () {
        expect($this->loan->user->email)->toBe($this->user->email);
    });
});
