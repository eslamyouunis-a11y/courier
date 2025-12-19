<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourierHandover extends Model
{
    protected $guarded = [];

    public function courier(): BelongsTo { return $this->belongsTo(Courier::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }

    public function items(): HasMany { return $this->hasMany(CourierHandoverItem::class, 'handover_id'); }
}
