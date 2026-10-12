<?php

use App\Models\Product;
use App\Models\User;

test('guest user sees mobile header user trigger, bottom nav, and global auth modal', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('class="active-login mobile-user-link"', false);
    $response->assertSee('header-mobile-fixed', false);
    $response->assertSee('id="global-auth-modal"', false);
    $response->assertSee('modal_email');
    $response->assertSee('modal_password');
    $response->assertSee('modal_reg_name');
});

test('authenticated user sees dashboard links instead of login modal', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertOk();
    $response->assertSee(route('dashboard'));
    $response->assertDontSee('id="global-auth-modal"', false);
});

test('shop page renders product grid and filters responsively', function () {
    $product = Product::factory()->create([
        'name' => 'Bamboo Pole High Quality',
        'unit_price' => 250,
    ]);

    $response = $this->get(route('shop'));

    $response->assertOk();
    $response->assertSee('Bamboo Pole High Quality');
    $response->assertSee('col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3', false);
    $response->assertSee('header-mobile-fixed', false);
});

test('product detail page includes whatsapp inquiry and store contact actions', function () {
    $product = Product::factory()->create([
        'name' => 'Treated Bamboo',
        'unit_price' => 500,
    ]);

    $response = $this->get(route('catalog.show', $product->id));

    $response->assertOk();
    $response->assertSee('Inquire on WhatsApp');
    $response->assertSee('Contact Us');
});

test('password fields across website forms have eye icon toggle buttons', function () {
    // 1. Home / Layout Modal
    $response = $this->get(route('home'));
    $response->assertOk();
    $response->assertSee("togglePasswordVisibility('modal_password', this)", false);
    $response->assertSee("togglePasswordVisibility('modal_reg_password', this)", false);
    $response->assertSee("togglePasswordVisibility('modal_reg_password_confirmation', this)", false);

    // 2. Dedicated Login / Register page
    $loginResponse = $this->get(route('login'));
    $loginResponse->assertOk();
    $loginResponse->assertSee("togglePasswordVisibility('page_login_password', this)", false);
    $loginResponse->assertSee("togglePasswordVisibility('page_reg_password', this)", false);
    $loginResponse->assertSee("togglePasswordVisibility('page_reg_password_confirmation', this)", false);

    // 3. Checkout page
    $checkoutResponse = $this->get(route('checkout'));
    $checkoutResponse->assertOk();
    $checkoutResponse->assertSee("togglePasswordVisibility('checkout_account_password', this)", false);

    // 4. My Account page
    $myAccountResponse = $this->get(route('my-account'));
    $myAccountResponse->assertOk();
    $myAccountResponse->assertSee("togglePasswordVisibility('account_password_current', this)", false);
    $myAccountResponse->assertSee("togglePasswordVisibility('account_password_1', this)", false);
    $myAccountResponse->assertSee("togglePasswordVisibility('account_password_2', this)", false);
});

