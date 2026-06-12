<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Note extends Model
{
    protected $guarded = [];

    public function notable(): MorphTo
    {
        return $this->morphTo();
    }

    /** 作者:員工 (User) 或客人 (Customer) */
    public function author(): MorphTo
    {
        return $this->morphTo();
    }

    public function getAuthorNameAttribute(): string
    {
        return $this->author?->name ?? '系統';
    }
}
