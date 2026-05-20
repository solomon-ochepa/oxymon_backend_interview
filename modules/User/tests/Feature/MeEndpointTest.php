<?php

use Modules\User\App\Models\User;

describe('GET /api/me', function () {
    describe('unauthenticated', function () {
        beforeEach(function () {
            $this->response = $this->getJson('/api/me');
        });

        test('returns 401', function () {
            $this->response->assertUnauthorized();
        });

        test('returns unauthenticated message', function () {
            $this->response->assertJson(['message' => 'Unauthenticated.']);
        });
    });

    describe('authenticated', function () {
        beforeEach(function () {
            $this->user = User::factory()->create();
            $this->response = $this->actingAs($this->user)->getJson('/api/me');
        });

        test('returns 200', function () {
            $this->response->assertOk();
        });

        test('response contains the correct id', function () {
            $this->response->assertJsonPath('id', $this->user->id);
        });

        test('response contains the correct name', function () {
            $this->response->assertJsonPath('name', $this->user->name);
        });

        test('response contains the correct email', function () {
            $this->response->assertJsonPath('email', $this->user->email);
        });

        test('response contains exactly id, name, and email', function () {
            expect(array_keys($this->response->json()))->toBe(['id', 'name', 'email']);
        });

        test('content type is JSON', function () {
            $this->response->assertHeader('Content-Type', 'application/json');
        });
    });

    describe('sensitive field exclusion', function () {
        beforeEach(function () {
            $this->response = $this->actingAs(User::factory()->create())->getJson('/api/me');
        });

        test('does not expose password', function () {
            $this->response->assertJsonMissingPath('password');
        });

        test('does not expose remember_token', function () {
            $this->response->assertJsonMissingPath('remember_token');
        });

        test('does not expose role', function () {
            $this->response->assertJsonMissingPath('role');
        });

        test('does not expose email_verified_at', function () {
            $this->response->assertJsonMissingPath('email_verified_at');
        });
    });

    describe('returns the authenticated user', function () {
        test('returns own data, not another user\'s', function () {
            $userA = User::factory()->create();
            User::factory()->create();

            $this->actingAs($userA)->getJson('/api/me')
                ->assertJsonPath('id', $userA->id)
                ->assertJsonPath('name', $userA->name);
        });
    });

    describe('role variations', function () {
        test('admin user can access the endpoint', function () {
            $admin = User::factory()->create(['role' => 'admin']);
            $this->actingAs($admin)->getJson('/api/me')
                ->assertOk()
                ->assertJsonPath('id', $admin->id);
        });

        test('unverified user can access the endpoint', function () {
            $this->actingAs(User::factory()->unverified()->create())
                ->getJson('/api/me')
                ->assertOk();
        });
    });

    describe('disallowed methods', function () {
        beforeEach(function () {
            $this->actor = $this->actingAs(User::factory()->create());
        });

        test('POST returns 405', function () {
            $this->actor->postJson('/api/me')->assertStatus(405);
        });

        test('PUT returns 405', function () {
            $this->actor->putJson('/api/me')->assertStatus(405);
        });

        test('DELETE returns 405', function () {
            $this->actor->deleteJson('/api/me')->assertStatus(405);
        });
    });
});
