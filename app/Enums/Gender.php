<?php

namespace App\Enums;

enum Gender: string
{
    case MALE = 'male';
    case FEMALE = 'female';
    case OTHER = 'other';

    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = strtolower(trim($value));

        return match($value) {
            'male', 'm', 'Male', 'MALE' => self::MALE->value,
            'female', 'f', 'Female', 'FEMALE' => self::FEMALE->value,
            'other', 'o', 'Other', 'OTHER' => self::OTHER->value,
            default => null
        };
    }
}
