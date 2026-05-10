<?php

namespace App\Services;

use App\Models\Asset;
use Illuminate\Support\Str;
use RuntimeException;

class QrCodeValueGenerator
{
    public function generate(): string
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $value = (string) Str::uuid();

            if (! Asset::withTrashed()->where('qr_code_value', $value)->exists()) {
                return $value;
            }
        }

        throw new RuntimeException('Unable to generate a unique QR code value.');
    }
}
