<?php

namespace App\Services;

use App\Models\Asset;
use RuntimeException;

class QrCodeValueGenerator
{
    public const LENGTH = 10;

    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    public function generate(): string
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $value = $this->randomValue();

            if (! Asset::withTrashed()->where('qr_code_value', $value)->exists()) {
                return $value;
            }
        }

        throw new RuntimeException('Unable to generate a unique QR code value.');
    }

    public static function normalize(null|string|int $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return strtoupper((string) $value);
    }

    public static function validationRules(): array
    {
        return [
            'string',
            'size:'.self::LENGTH,
            'regex:/^[A-Z0-9]{'.self::LENGTH.'}$/',
        ];
    }

    public static function routePattern(): string
    {
        return '[A-Za-z0-9]{'.self::LENGTH.'}';
    }

    private function randomValue(): string
    {
        $value = '';
        $maxIndex = strlen(self::ALPHABET) - 1;

        for ($index = 0; $index < self::LENGTH; $index++) {
            $value .= self::ALPHABET[random_int(0, $maxIndex)];
        }

        return $value;
    }
}
