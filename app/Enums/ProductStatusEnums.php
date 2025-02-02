<?php

namespace App\Enums;

enum ProductStatusEnums: string
{
    case Published = "published";
    case Draft = "draft";

    public static function labels(): array
    {
        return [
            self::Published->value => __("Published"),
            self::Draft->value => __("Draft"),
        ];
    }

    public static function colors(): array
    {
        return [
            'gray' => self::Draft->value,
            'success' => self::Published->value,
        ];
    }
}
