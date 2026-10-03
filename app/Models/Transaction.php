<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_FAILED = 'failed';

    public const METHOD_CASH = 'cash';

    public const METHOD_QRIS = 'qris';

    protected $fillable = [
        'user_id',
        'total_price',
        'method',
        'status',
        'external_id',
        'paid_at',
        'expires_at',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeBetween(Builder $query, $start, $end): Builder
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isExpired(): bool
    {
        return $this->isPending() && $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PAID => 'LUNAS',
            self::STATUS_PENDING => 'MENUNGGU BAYAR',
            self::STATUS_FAILED => 'GAGAL',
            default => strtoupper((string) $this->status),
        };
    }

    public function methodLabel(): string
    {
        return $this->method === self::METHOD_QRIS ? 'QRIS' : 'TUNAI';
    }

    public function itemCount(): int
    {
        return (int) $this->details()->sum('quantity');
    }

    /**
     * Estimasi keuntungan (harga jual - harga beli) x jumlah.
     */
    public function profit(): float
    {
        return (float) $this->details->sum(function ($detail) {
            $buyPrice = $detail->product ? (float) $detail->product->buy_price : 0;

            return ((float) $detail->price - $buyPrice) * $detail->quantity;
        });
    }
}
