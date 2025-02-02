<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItems extends Model
{
    public $timestamps = false;
    protected $casts = [
        'variation_type_option_ids'=> 'array'
    ];
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
