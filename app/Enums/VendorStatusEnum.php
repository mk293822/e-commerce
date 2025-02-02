<?php

namespace App\Enums;

enum VendorStatusEnum: string
{
    case Approved = 'Approved';
    case Pending = 'Pending';
    case Rejected = 'Rejected';
}
