<?php

namespace App\Models;

use App\Enums\RepairOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RepairOrder extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => RepairOrderStatus::class,
            'date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (RepairOrder $order) {
            $order->order_no ??= static::nextOrderNo();
        });
    }

    /** 產生單號:RO-202606-0001 */
    public static function nextOrderNo(): string
    {
        $prefix = 'RO-'.now()->format('Ym').'-';
        $last = static::withTrashed()
            ->where('order_no', 'like', $prefix.'%')
            ->orderByDesc('order_no')
            ->value('order_no');
        $seq = $last ? (int) substr($last, -4) + 1 : 1;

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /** 由明細重算金額 */
    public function recalcTotals(): void
    {
        $this->subtotal = (int) $this->items()->sum('subtotal');
        $this->total = max(0, $this->subtotal - $this->discount);
        $this->paid_amount = (int) $this->payments()->sum('amount');
        $this->saveQuietly();
    }

    /** 未收金額 */
    public function getBalanceAttribute(): int
    {
        return max(0, $this->total - $this->paid_amount);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RepairOrderItem::class)->orderBy('sort');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'imageable');
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable')->latest();
    }
}
