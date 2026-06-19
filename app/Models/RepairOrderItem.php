<?php

namespace App\Models;

use App\Enums\RepairItemType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class RepairOrderItem extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'type' => RepairItemType::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (RepairOrderItem $item) {
            $item->subtotal = $item->qty * $item->price;
        });

        $recalc = fn (RepairOrderItem $item) => $item->repairOrder?->recalcTotals();
        static::saved($recalc);
        static::deleted($recalc);
    }

    public function repairOrder(): BelongsTo
    {
        return $this->belongsTo(RepairOrder::class);
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'imageable');
    }
}
