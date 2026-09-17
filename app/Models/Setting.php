<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get a setting value by key with optional fallback.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember('setting_' . $key, 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return ($setting && $setting->value !== null && $setting->value !== '') ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, mixed $value): static
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget('setting_' . $key);
        Cache::forget('store_details');

        return $setting;
    }

    /**
     * Get all store branding & invoice credential details in a single array.
     */
    public static function getStoreDetails(): array
    {
        return Cache::remember('store_details', 3600, function () {
            return [
                'storeName' => static::get('store_name', config('khatabook.store_name', 'Ashok Kumar Baans Store')),
                'storeSubtitle' => static::get('store_subtitle', config('khatabook.store_subtitle', 'Direct Timber Merchant • Raw Bamboo, Ghodi, Chaali & Siddhi')),
                'storeAddress' => static::get('store_address', config('khatabook.store_address', 'House No 2755, Opposite Gaushala Road, Janak Puri, Karnal, Haryana - 132001')),
                'storePhone' => static::get('store_phone', config('khatabook.store_phone', 'Ashok Kumar: 9254998000, 9255523276 | Ansh: 8950304888')),
                'storeEmail' => static::get('store_email', config('khatabook.store_email', 'anshgohri8950@gmail.com')),
                'storeTerms' => static::get('store_terms', config('khatabook.store_terms', "1. Goods once sold are strictly governed under timber yard standard policies.\n2. Raw bamboo poles & Ghodi trestles are checked before dispatch.\n3. Thank you for doing business with Ashok Kumar Baans Store!")),
                'invoicePrefix' => static::get('invoice_prefix', config('khatabook.invoice_prefix', 'INV-')),
            ];
        });
    }
}
