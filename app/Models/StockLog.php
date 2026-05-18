<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'change_type',
        'quantity',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
