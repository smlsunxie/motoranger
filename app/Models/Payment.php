<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'method' => PaymentMethod::class,
            'paid_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        $recalc = fn (Payment $payment) => $payment->repairOrder?->recalcTotals();
        static::saved($recalc);
        static::deleted($recalc);
    }

    public function repairOrder(): BelongsTo
    {
        return $this->belongsTo(RepairOrder::class);
    }
}
