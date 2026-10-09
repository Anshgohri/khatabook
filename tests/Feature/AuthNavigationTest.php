<?php

use App\Models\User;

test('guest user sees login link in website header', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('Login', false);
    $response->assertDontSee('Go to Dashboard', false);
});

test('authenticated user sees Go to Dashboard button instead of Login link in website header', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/');

    $response->assertStatus(200);
    $response->assertSee('Go to Dashboard', false);
    $response->assertSee(route('dashboard'), false);
});

test('authenticated user visiting login page sees Go to Dashboard button', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/login');

    $response->assertStatus(200);
    $response->assertSee('You are already logged in', false);
    $response->assertSee('Go to Dashboard', false);
});
