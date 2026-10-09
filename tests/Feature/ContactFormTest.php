<?php

use App\Models\ContactInquiry;

test('contact page loads successfully', function () {
    $response = $this->get(route('contact'));

    $response->assertStatus(200);
    $response->assertSee('Send Us Your Questions!');
    $response->assertSee('Submit Inquiry');
});

test('submitting contact form creates database entry and redirects back with success message', function () {
    $payload = [
        'name' => 'John Doe',
        'phone' => '+1234567890',
        'email' => 'john@example.com',
        'inquiry_type' => 'Scaffolding Rental',
        'message' => 'Need 50 bamboo poles for construction site next week.',
    ];

    $response = $this->post(route('contact.store'), $payload);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('contact_inquiries', [
        'name' => 'John Doe',
        'phone' => '+1234567890',
        'email' => 'john@example.com',
        'inquiry_type' => 'Scaffolding Rental',
        'message' => 'Need 50 bamboo poles for construction site next week.',
        'status' => 'pending',
    ]);
});

test('contact form validation fails with invalid data', function () {
    $response = $this->post(route('contact.store'), [
        'name' => '',
        'phone' => '',
        'message' => '',
    ]);

    $response->assertSessionHasErrors(['name', 'phone', 'message']);
});
