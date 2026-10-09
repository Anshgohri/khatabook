<?php

use App\Models\Product;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

test('product image_url returns correct path for local storage', function () {
    Config::set('filesystems.default', 'public');

    $product = Product::factory()->create([
        'image_path' => 'products/sample.jpg',
    ]);

    expect($product->image_url)->toBe(Storage::url('products/sample.jpg'));
    expect($product->image_url)->toContain('/storage/products/sample.jpg');
});

test('product image_url returns S3 bucket URL when default disk is s3', function () {
    Config::set('filesystems.default', 's3');
    Config::set('filesystems.disks.s3.bucket', 'my-bucket');
    Config::set('filesystems.disks.s3.region', 'us-east-1');
    Config::set('filesystems.disks.s3.url', 'https://my-bucket.s3.us-east-1.amazonaws.com');

    $product = Product::factory()->create([
        'image_path' => 'products/s3-image.png',
    ]);

    expect($product->image_url)->toBe('https://my-bucket.s3.us-east-1.amazonaws.com/products/s3-image.png');
});

test('product image_url returns correct URL when image_path is full HTTP URL', function () {
    $fullUrl = 'https://example.com/storage/products/custom.png';

    $product = Product::factory()->create([
        'image_path' => $fullUrl,
    ]);

    expect($product->image_url)->toBe($fullUrl);
});

test('product image_url returns default fallback asset when image_path is null', function () {
    $product = Product::factory()->create([
        'image_path' => null,
    ]);

    expect($product->image_url)->toBe(asset('website/media/bamboo-6.jpg'));
    expect($product->has_custom_image)->toBeFalse();
});

test('shop, details, and home views render Storage::url for products with images', function () {
    $product = Product::factory()->create([
        'name' => 'Test S3 Product',
        'image_path' => 'products/test-s3.jpg',
        'unit_price' => 299.99,
    ]);

    $expectedUrl = Storage::url('products/test-s3.jpg');

    $this->get(route('shop'))
        ->assertStatus(200)
        ->assertSee($expectedUrl, false);

    $this->get(route('catalog.show', $product->id))
        ->assertStatus(200)
        ->assertSee($expectedUrl, false);

    $this->get(route('home'))
        ->assertStatus(200)
        ->assertSee($expectedUrl, false);
});
