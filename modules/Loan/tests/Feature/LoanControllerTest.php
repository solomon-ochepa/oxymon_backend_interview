<?php

use Illuminate\Support\Arr;
use Modules\Loan\App\Models\Loan;
use Modules\User\App\Models\User;

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function validLoanPayload(int $userId): array
{
    return [
        'user_id' => $userId,
        'amount' => 10000,
        'interest' => 5.5,
        'term' => 24,
    ];
}

// ---------------------------------------------------------------------------
// GET /api/loans  (index)
// ---------------------------------------------------------------------------

describe('GET /api/loans', function () {
    describe('unauthenticated', function () {
        beforeEach(fn () => $this->response = $this->getJson('/api/loans'));

        test('returns 401', fn () => $this->response->assertUnauthorized());
        test('returns unauthenticated message', fn () => $this->response->assertJson(['message' => 'Unauthenticated.']));
    });

    describe('authenticated', function () {
        beforeEach(function () {
            $this->user = User::factory()->create();
            Loan::factory()->count(3)->forUser($this->user)->create([
                'created_at' => now()->subSeconds(2),
            ]);
            // One newer loan to make ordering deterministic
            Loan::factory()->forUser($this->user)->create(['created_at' => now()]);
            $this->response = $this->actingAs($this->user)->getJson('/api/loans');
        });

        test('returns 200', fn () => $this->response->assertOk());

        test('response has data, links, and meta', function () {
            $this->response->assertJsonStructure(['data', 'links', 'meta']);
        });

        test('returns all loans', function () {
            expect(count($this->response->json('data')))->toBe(4);
        });

        test('each loan has expected fields', function () {
            $this->response->assertJsonStructure([
                'data' => [['id', 'user_id', 'amount', 'interest', 'term', 'status', 'created_at', 'updated_at']],
            ]);
        });

        test('loans are ordered latest first', function () {
            $ids = collect($this->response->json('data'))->pluck('id');
            expect($ids->first())->toBeGreaterThan($ids->last());
        });

        test('returns empty data when no loans exist', function () {
            Loan::query()->delete();
            $this->actingAs($this->user)->getJson('/api/loans')
                ->assertJsonPath('data', []);
        });

        test('paginated meta contains current_page 1', function () {
            $this->response->assertJsonPath('meta.current_page', 1);
        });

        test('paginates at 15 per page', function () {
            $this->response->assertJsonPath('meta.per_page', 15);
        });
    });

    describe('includes loans from all users', function () {
        test('index returns loans belonging to different users', function () {
            Loan::factory()->forUser(User::factory()->create())->create();
            Loan::factory()->forUser(User::factory()->create())->create();

            $this->actingAs(User::factory()->create())->getJson('/api/loans')
                ->assertOk()
                ->assertJsonCount(2, 'data');
        });
    });
});

// ---------------------------------------------------------------------------
// GET /api/loans/me  (myLoans)
// ---------------------------------------------------------------------------

describe('GET /api/loans/me', function () {
    describe('unauthenticated', function () {
        test('returns 401', fn () => $this->getJson('/api/loans/me')->assertUnauthorized());
    });

    describe('authenticated', function () {
        beforeEach(function () {
            $this->user = User::factory()->create();
            $this->other = User::factory()->create();

            Loan::factory()->forUser($this->user)->create(['created_at' => now()->subSeconds(1)]);
            Loan::factory()->forUser($this->user)->create(['created_at' => now()]);
            Loan::factory()->count(3)->forUser($this->other)->create();

            $this->response = $this->actingAs($this->user)->getJson('/api/loans/me');
        });

        test('returns 200', fn () => $this->response->assertOk());

        test('returns only the authenticated user\'s loans', function () {
            expect(count($this->response->json('data')))->toBe(2);
        });

        test('does not include other users\' loans', function () {
            collect($this->response->json('data'))->each(
                fn ($loan) => expect($loan['user_id'])->toBe($this->user->id)
            );
        });

        test('loans are ordered latest first', function () {
            $ids = collect($this->response->json('data'))->pluck('id');
            expect($ids->first())->toBeGreaterThan($ids->last());
        });

        test('returns empty data when user has no loans', function () {
            $fresh = User::factory()->create();
            $this->actingAs($fresh)->getJson('/api/loans/me')
                ->assertJsonPath('data', []);
        });

        test('response is paginated', function () {
            $this->response->assertJsonStructure(['data', 'links', 'meta']);
        });
    });
});

// ---------------------------------------------------------------------------
// POST /api/loans  (store)
// ---------------------------------------------------------------------------

describe('POST /api/loans', function () {
    describe('unauthenticated', function () {
        test('returns 401', fn () => $this->postJson('/api/loans', [])->assertUnauthorized());
    });

    describe('validation', function () {
        beforeEach(fn () => $this->user = User::factory()->create());

        test('user_id is required', function () {
            $this->actingAs($this->user)
                ->postJson('/api/loans', Arr::except(validLoanPayload($this->user->id), ['user_id']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('user_id');
        });

        test('user_id must exist in users table', function () {
            $this->actingAs($this->user)
                ->postJson('/api/loans', array_merge(validLoanPayload($this->user->id), ['user_id' => 999999]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('user_id');
        });

        test('amount is required', function () {
            $this->actingAs($this->user)
                ->postJson('/api/loans', Arr::except(validLoanPayload($this->user->id), ['amount']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('amount');
        });

        test('amount must be numeric', function () {
            $this->actingAs($this->user)
                ->postJson('/api/loans', array_merge(validLoanPayload($this->user->id), ['amount' => 'abc']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('amount');
        });

        test('amount minimum is 1', function () {
            $this->actingAs($this->user)
                ->postJson('/api/loans', array_merge(validLoanPayload($this->user->id), ['amount' => 0]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('amount');
        });

        test('amount maximum is 9999999999', function () {
            $this->actingAs($this->user)
                ->postJson('/api/loans', array_merge(validLoanPayload($this->user->id), ['amount' => 10_000_000_000]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('amount');
        });

        test('interest is required', function () {
            $this->actingAs($this->user)
                ->postJson('/api/loans', Arr::except(validLoanPayload($this->user->id), ['interest']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('interest');
        });

        test('interest minimum is 0', function () {
            $this->actingAs($this->user)
                ->postJson('/api/loans', array_merge(validLoanPayload($this->user->id), ['interest' => -1]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('interest');
        });

        test('interest maximum is 100', function () {
            $this->actingAs($this->user)
                ->postJson('/api/loans', array_merge(validLoanPayload($this->user->id), ['interest' => 101]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('interest');
        });

        test('interest of exactly 0 is valid', function () {
            $this->actingAs($this->user)
                ->postJson('/api/loans', array_merge(validLoanPayload($this->user->id), ['interest' => 0]))
                ->assertStatus(201);
        });

        test('interest of exactly 100 is valid', function () {
            $this->actingAs($this->user)
                ->postJson('/api/loans', array_merge(validLoanPayload($this->user->id), ['interest' => 100]))
                ->assertStatus(201);
        });

        test('term is required', function () {
            $this->actingAs($this->user)
                ->postJson('/api/loans', Arr::except(validLoanPayload($this->user->id), ['term']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('term');
        });

        test('term must be integer', function () {
            $this->actingAs($this->user)
                ->postJson('/api/loans', array_merge(validLoanPayload($this->user->id), ['term' => 1.5]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('term');
        });

        test('term minimum is 1', function () {
            $this->actingAs($this->user)
                ->postJson('/api/loans', array_merge(validLoanPayload($this->user->id), ['term' => 0]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('term');
        });

        test('term maximum is 600', function () {
            $this->actingAs($this->user)
                ->postJson('/api/loans', array_merge(validLoanPayload($this->user->id), ['term' => 601]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('term');
        });

        test('status is optional', function () {
            $this->actingAs($this->user)
                ->postJson('/api/loans', Arr::except(validLoanPayload($this->user->id), ['status']))
                ->assertStatus(201);
        });

        test('status must be a valid enum value', function () {
            $this->actingAs($this->user)
                ->postJson('/api/loans', array_merge(validLoanPayload($this->user->id), ['status' => 'invalid']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('status');
        });
    });

    describe('success', function () {
        beforeEach(function () {
            $this->user = User::factory()->create();
            $this->response = $this->actingAs($this->user)
                ->postJson('/api/loans', validLoanPayload($this->user->id));
        });

        test('returns 201', fn () => $this->response->assertStatus(201));

        test('response wraps loan in data key', function () {
            $this->response->assertJsonStructure([
                'data' => ['id', 'user_id', 'amount', 'interest', 'term', 'status'],
            ]);
        });

        test('persists loan to the database', fn () => expect(Loan::count())->toBe(1));

        test('loan is owned by the authenticated user', function () {
            expect(Loan::first()->user_id)->toBe($this->user->id);
        });

        test('default status is pending', function () {
            expect($this->response->json('data.status'))->toBe('pending');
        });

        test('amount is saved correctly', function () {
            expect($this->response->json('data.amount'))->toBe('10000.00');
        });

        test('explicit status is saved when provided', function () {
            $user = User::factory()->create();
            $response = $this->actingAs($user)
                ->postJson('/api/loans', array_merge(validLoanPayload($user->id), ['status' => 'approved']));
            expect($response->json('data.status'))->toBe('approved');
        });

        test('response does not contain borrower_name', function () {
            $this->response->assertJsonMissingPath('data.borrower_name');
        });

        test('response does not contain borrower_email', function () {
            $this->response->assertJsonMissingPath('data.borrower_email');
        });
    });
});

// ---------------------------------------------------------------------------
// GET /api/loans/{loan}  (show)
// ---------------------------------------------------------------------------

describe('GET /api/loans/{loan}', function () {
    describe('unauthenticated', function () {
        test('returns 401', function () {
            $loan = Loan::factory()->create();
            $this->getJson("/api/loans/{$loan->id}")->assertUnauthorized();
        });
    });

    describe('authenticated', function () {
        beforeEach(function () {
            $this->user = User::factory()->create();
            $this->loan = Loan::factory()->forUser($this->user)->create(['status' => 'active']);
            $this->response = $this->actingAs($this->user)
                ->getJson("/api/loans/{$this->loan->id}");
        });

        test('returns 200', fn () => $this->response->assertOk());

        test('returns loan wrapped in data key', function () {
            $this->response->assertJsonStructure([
                'data' => ['id', 'user_id', 'amount', 'interest', 'term', 'status'],
            ]);
        });

        test('returns correct loan id', fn () => $this->response->assertJsonPath('data.id', $this->loan->id));

        test('returns correct user_id', fn () => $this->response->assertJsonPath('data.user_id', $this->user->id));

        test('returns correct status', fn () => $this->response->assertJsonPath('data.status', 'active'));

        test('any authenticated user can view any loan', function () {
            $other = Loan::factory()->forUser(User::factory()->create())->create();
            $this->actingAs($this->user)->getJson("/api/loans/{$other->id}")->assertOk();
        });

        test('response does not contain borrower_name', function () {
            $this->response->assertJsonMissingPath('data.borrower_name');
        });
    });

    describe('non-existent loan', function () {
        test('returns 404', function () {
            $this->actingAs(User::factory()->create())
                ->getJson('/api/loans/999999')
                ->assertNotFound();
        });
    });
});

// ---------------------------------------------------------------------------
// PUT /api/loans/{loan}  (update)
// ---------------------------------------------------------------------------

describe('PUT /api/loans/{loan}', function () {
    describe('unauthenticated', function () {
        test('returns 401', function () {
            $loan = Loan::factory()->create();
            $this->putJson("/api/loans/{$loan->id}", [])->assertUnauthorized();
        });
    });

    describe('success', function () {
        beforeEach(function () {
            $this->user = User::factory()->create();
            $this->loan = Loan::factory()->forUser($this->user)->create(['status' => 'pending']);
        });

        test('returns 200', function () {
            $this->actingAs($this->user)
                ->putJson("/api/loans/{$this->loan->id}", array_merge(validLoanPayload($this->user->id), ['status' => 'approved']))
                ->assertOk();
        });

        test('response wraps updated loan in data key', function () {
            $this->actingAs($this->user)
                ->putJson("/api/loans/{$this->loan->id}", validLoanPayload($this->user->id))
                ->assertJsonStructure(['data' => ['id', 'status', 'amount']]);
        });

        test('can update amount', function () {
            $this->actingAs($this->user)
                ->putJson("/api/loans/{$this->loan->id}", array_merge(validLoanPayload($this->user->id), ['amount' => 99999]));
            expect($this->loan->fresh()->amount)->toBe('99999.00');
        });

        test('can update status to approved', function () {
            $this->actingAs($this->user)
                ->putJson("/api/loans/{$this->loan->id}", array_merge(validLoanPayload($this->user->id), ['status' => 'approved']));
            expect($this->loan->fresh()->status)->toBe('approved');
        });

        test('can update status to paid', function () {
            $this->actingAs($this->user)
                ->putJson("/api/loans/{$this->loan->id}", array_merge(validLoanPayload($this->user->id), ['status' => 'paid']));
            expect($this->loan->fresh()->status)->toBe('paid');
        });

        test('can update term', function () {
            $this->actingAs($this->user)
                ->putJson("/api/loans/{$this->loan->id}", array_merge(validLoanPayload($this->user->id), ['term' => 60]));
            expect($this->loan->fresh()->term)->toBe(60);
        });

        test('can omit optional fields on partial update', function () {
            $this->actingAs($this->user)
                ->putJson("/api/loans/{$this->loan->id}", ['user_id' => $this->user->id, 'amount' => 5000])
                ->assertOk();
        });
    });

    describe('validation', function () {
        beforeEach(function () {
            $this->user = User::factory()->create();
            $this->loan = Loan::factory()->forUser($this->user)->create();
        });

        test('user_id is required', function () {
            $this->actingAs($this->user)
                ->putJson("/api/loans/{$this->loan->id}", ['amount' => 5000])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('user_id');
        });

        test('user_id must exist', function () {
            $this->actingAs($this->user)
                ->putJson("/api/loans/{$this->loan->id}", ['user_id' => 999999])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('user_id');
        });

        test('status must be valid when provided', function () {
            $this->actingAs($this->user)
                ->putJson("/api/loans/{$this->loan->id}", ['user_id' => $this->user->id, 'status' => 'unknown'])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('status');
        });

        test('amount must be at least 1 when provided', function () {
            $this->actingAs($this->user)
                ->putJson("/api/loans/{$this->loan->id}", ['user_id' => $this->user->id, 'amount' => 0])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('amount');
        });

        test('interest must not exceed 100 when provided', function () {
            $this->actingAs($this->user)
                ->putJson("/api/loans/{$this->loan->id}", ['user_id' => $this->user->id, 'interest' => 101])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('interest');
        });

        test('term must not exceed 600 when provided', function () {
            $this->actingAs($this->user)
                ->putJson("/api/loans/{$this->loan->id}", ['user_id' => $this->user->id, 'term' => 601])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('term');
        });
    });

    describe('non-existent loan', function () {
        test('returns 404', function () {
            $user = User::factory()->create();
            $this->actingAs($user)
                ->putJson('/api/loans/999999', validLoanPayload($user->id))
                ->assertNotFound();
        });
    });
});

// ---------------------------------------------------------------------------
// DELETE /api/loans/{loan}  (destroy)
// ---------------------------------------------------------------------------

describe('DELETE /api/loans/{loan}', function () {
    describe('unauthenticated', function () {
        test('returns 401', function () {
            $loan = Loan::factory()->create();
            $this->deleteJson("/api/loans/{$loan->id}")->assertUnauthorized();
        });
    });

    describe('success', function () {
        beforeEach(function () {
            $this->user = User::factory()->create();
            $this->loan = Loan::factory()->forUser($this->user)->create();
            $this->response = $this->actingAs($this->user)
                ->deleteJson("/api/loans/{$this->loan->id}");
        });

        test('returns 204', fn () => $this->response->assertStatus(204));
        test('returns no content body', fn () => expect($this->response->getContent())->toBe(''));
        test('loan is removed from the database', fn () => expect(Loan::find($this->loan->id))->toBeNull());
        test('loan count decrements to zero', fn () => expect(Loan::count())->toBe(0));
    });

    describe('non-existent loan', function () {
        test('returns 404', function () {
            $this->actingAs(User::factory()->create())
                ->deleteJson('/api/loans/999999')
                ->assertNotFound();
        });
    });

    describe('any authenticated user can delete any loan', function () {
        test('user can delete another user\'s loan', function () {
            $owner = User::factory()->create();
            $loan = Loan::factory()->forUser($owner)->create();

            $this->actingAs(User::factory()->create())
                ->deleteJson("/api/loans/{$loan->id}")
                ->assertStatus(204);

            expect(Loan::find($loan->id))->toBeNull();
        });
    });
});
