<?php

namespace App\Enums;

enum ProductVariationTypeEnum: string
{
    case Radio='radio';
    case Select='select';
    case Image='image';

    public static function labels(): array
    {
        return [
          self::Radio->value => __('Radio'),
          self::Select->value => __('Select'),
          self::Image->value => __('Image'),
        ];
    }
}
