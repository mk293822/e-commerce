<?php

namespace App\Enums;

enum PermissionEnum: string
{
    case ApprovedVendor = 'approved-vendor';
    case SellProduct = 'sell-product';
    case BuyProduct = 'buy-product';
}
