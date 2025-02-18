<?php

namespace App\Enums;

enum Gender: string
{
    case MALE = 'male';
    case FEMALE = 'female';
    case OTHER = 'other';

    public static function fromValue(?string $value): ?self
    {
        if ($value === null) {
            return null;
        }

        // Clean the input - trim whitespace and convert to lowercase
        $value = strtolower(trim($value));

        return match($value) {
            'male', 'm', 'Male', 'MALE' => self::MALE,
            'female', 'f', 'Female', 'FEMALE' => self::FEMALE,
            'other', 'o', 'Other', 'OTHER' => self::OTHER,
            default => throw new \ValueError("Invalid gender value: {$value}")
        };
    }

    public function label(): string
    {
        return match($this) {
            self::MALE => 'Male',
            self::FEMALE => 'Female',
            self::OTHER => 'Other'
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return [
            'male' => 'Male',
            'female' => 'Female',
            'other' => 'Other'
        ];
    }
}
