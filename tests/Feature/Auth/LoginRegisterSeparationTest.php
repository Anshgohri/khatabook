<?php

test('login page shows only login form and not register form on page', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
    $response->assertSee('id="page_login_form"', false);
    $response->assertDontSee('id="page_register_form"', false);
    $response->assertSee('Username or email address');
    $response->assertSee('Register here');
});

test('register page shows only register form and not login form on page', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
    $response->assertSee('id="page_register_form"', false);
    $response->assertDontSee('id="page_login_form"', false);
    $response->assertSee('Login here');
});

test('ajax login with invalid credentials returns 422 json error without redirect', function () {
    $response = $this->postJson(route('login.store'), [
        'email' => 'wrong@example.com',
        'password' => 'invalid-password',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
    $response->assertJsonPath('errors.email.0', 'These credentials do not match our records.');
});

test('regular form post with invalid credentials redirects back to login route', function () {
    $response = $this->from(route('login'))->post(route('login.store'), [
        'email' => 'wrong@example.com',
        'password' => 'invalid-password',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors('email');
});

test('regular form post with invalid registration data redirects back to register route', function () {
    $response = $this->from(route('register'))->post(route('register.store'), [
        'name' => '',
        'email' => 'invalid-email',
        'password' => 'short',
        'password_confirmation' => 'mismatch',
    ]);

    $response->assertRedirect(route('register'));
    $response->assertSessionHasErrors(['name', 'email', 'password']);
});
