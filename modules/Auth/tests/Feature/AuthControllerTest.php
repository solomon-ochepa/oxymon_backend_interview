<?php

use Modules\User\App\Models\User;

describe('POST /api/register', function () {
    beforeEach(function () {
        $this->payload = [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];
    });

    test('returns 201 with user and token on valid payload', function () {
        $this->postJson('/api/register', $this->payload)
            ->assertStatus(201)
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);
    });

    test('response user contains correct name and email', function () {
        $response = $this->postJson('/api/register', $this->payload)->assertStatus(201);

        expect($response->json('user.name'))->toBe('Ada Lovelace');
        expect($response->json('user.email'))->toBe('ada@example.com');
    });

    test('response user does not contain password or sensitive fields', function () {
        $response = $this->postJson('/api/register', $this->payload)->assertStatus(201);

        expect($response->json('user'))->not->toHaveKey('password');
        expect($response->json('user'))->not->toHaveKey('remember_token');
        expect($response->json('user'))->not->toHaveKey('role');
    });

    test('response user contains exactly id, name, email', function () {
        $response = $this->postJson('/api/register', $this->payload)->assertStatus(201);

        expect(array_keys($response->json('user')))->toBe(['id', 'name', 'email']);
    });

    test('token is a non-empty string', function () {
        $response = $this->postJson('/api/register', $this->payload)->assertStatus(201);

        expect($response->json('token'))->toBeString()->not->toBeEmpty();
    });

    test('creates user in the database', function () {
        $this->postJson('/api/register', $this->payload)->assertStatus(201);

        $this->assertDatabaseHas('users', ['email' => 'ada@example.com']);
    });

    describe('validation', function () {
        test('returns 422 when name is missing', function () {
            $this->postJson('/api/register', array_merge($this->payload, ['name' => '']))
                ->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
        });

        test('returns 422 when email is missing', function () {
            $this->postJson('/api/register', array_merge($this->payload, ['email' => '']))
                ->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });

        test('returns 422 when email is invalid format', function () {
            $this->postJson('/api/register', array_merge($this->payload, ['email' => 'not-an-email']))
                ->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });

        test('returns 422 when email is already taken', function () {
            User::factory()->create(['email' => 'ada@example.com']);

            $this->postJson('/api/register', $this->payload)
                ->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });

        test('returns 422 when password is missing', function () {
            $this->postJson('/api/register', array_merge($this->payload, ['password' => '', 'password_confirmation' => '']))
                ->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
        });

        test('returns 422 when password confirmation does not match', function () {
            $this->postJson('/api/register', array_merge($this->payload, ['password_confirmation' => 'DifferentPass1!']))
                ->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
        });

        test('returns 422 when all fields are missing', function () {
            $this->postJson('/api/register', [])
                ->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'email', 'password']);
        });
    });
});

describe('POST /api/login', function () {
    beforeEach(function () {
        $this->user = User::factory()->create([
            'email' => 'ada@example.com',
            'password' => bcrypt('Password123!'),
        ]);

        $this->payload = [
            'email' => 'ada@example.com',
            'password' => 'Password123!',
        ];
    });

    test('returns 200 with user and token on valid credentials', function () {
        $this->postJson('/api/login', $this->payload)
            ->assertStatus(200)
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);
    });

    test('response user contains correct id, name, email', function () {
        $response = $this->postJson('/api/login', $this->payload)->assertStatus(200);

        expect($response->json('user.id'))->toBe($this->user->id);
        expect($response->json('user.name'))->toBe($this->user->name);
        expect($response->json('user.email'))->toBe($this->user->email);
    });

    test('response user contains exactly id, name, email', function () {
        $response = $this->postJson('/api/login', $this->payload)->assertStatus(200);

        expect(array_keys($response->json('user')))->toBe(['id', 'name', 'email']);
    });

    test('response user does not contain password or sensitive fields', function () {
        $response = $this->postJson('/api/login', $this->payload)->assertStatus(200);

        expect($response->json('user'))->not->toHaveKey('password');
        expect($response->json('user'))->not->toHaveKey('remember_token');
        expect($response->json('user'))->not->toHaveKey('role');
    });

    test('token is a non-empty string', function () {
        $response = $this->postJson('/api/login', $this->payload)->assertStatus(200);

        expect($response->json('token'))->toBeString()->not->toBeEmpty();
    });

    describe('validation', function () {
        test('returns 422 when email is missing', function () {
            $this->postJson('/api/login', ['password' => 'Password123!'])
                ->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });

        test('returns 422 when password is missing', function () {
            $this->postJson('/api/login', ['email' => 'ada@example.com'])
                ->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
        });

        test('returns 422 when all fields are missing', function () {
            $this->postJson('/api/login', [])
                ->assertStatus(422)
                ->assertJsonValidationErrors(['email', 'password']);
        });
    });

    describe('invalid credentials', function () {
        test('returns 422 when password is wrong', function () {
            $this->postJson('/api/login', array_merge($this->payload, ['password' => 'WrongPassword1!']))
                ->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });

        test('returns 422 when email does not exist', function () {
            $this->postJson('/api/login', array_merge($this->payload, ['email' => 'nobody@example.com']))
                ->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });

        test('error message is The provided credentials are incorrect', function () {
            $response = $this->postJson('/api/login', array_merge($this->payload, ['password' => 'WrongPassword1!']));

            expect($response->json('errors.email.0'))->toBe('The provided credentials are incorrect.');
        });
    });
});

describe('POST /api/logout', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('api')->plainTextToken;
    });

    test('returns 204 when authenticated', function () {
        $this->withToken($this->token)
            ->postJson('/api/logout')
            ->assertStatus(204);
    });

    test('deletes the current access token', function () {
        $this->withToken($this->token)
            ->postJson('/api/logout')
            ->assertStatus(204);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    });

    test('only deletes the current token, not other tokens', function () {
        $otherToken = $this->user->createToken('other')->plainTextToken;

        $this->withToken($this->token)
            ->postJson('/api/logout')
            ->assertStatus(204);

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->withToken($otherToken)
            ->postJson('/api/logout')
            ->assertStatus(204);
    });

    describe('unauthenticated', function () {
        test('returns 401 without token', function () {
            $this->postJson('/api/logout')
                ->assertStatus(401);
        });

        test('returns 401 with invalid token', function () {
            $this->withToken('invalid-token-value')
                ->postJson('/api/logout')
                ->assertStatus(401);
        });

        test('returns 401 with expired/deleted token', function () {
            $this->user->tokens()->delete();

            $this->withToken($this->token)
                ->postJson('/api/logout')
                ->assertStatus(401);
        });
    });
});
