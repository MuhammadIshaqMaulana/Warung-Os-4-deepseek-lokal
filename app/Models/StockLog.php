<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockLog extends Model
{
    use SoftDeletes;

    public const TYPE_IN = 'in';

    public const TYPE_OUT = 'out';

    protected $fillable = [
        'product_id',
        'change_type',
        'quantity',
        'note',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function directionLabel(): string
    {
        return $this->change_type === self::TYPE_IN ? 'Masuk' : 'Keluar';
    }
}
