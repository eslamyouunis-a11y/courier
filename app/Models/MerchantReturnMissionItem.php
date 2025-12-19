<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchantReturnMissionItem extends Model
{
    protected $guarded = [];

    public function mission(): BelongsTo { return $this->belongsTo(MerchantReturnMission::class, 'mission_id'); }
    public function shipment(): BelongsTo { return $this->belongsTo(Shipment::class); }
}
