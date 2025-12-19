<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourierHandoverItem extends Model
{
    protected $guarded = [];

    public function handover(): BelongsTo { return $this->belongsTo(CourierHandover::class, 'handover_id'); }
    public function shipment(): BelongsTo { return $this->belongsTo(Shipment::class); }
}
