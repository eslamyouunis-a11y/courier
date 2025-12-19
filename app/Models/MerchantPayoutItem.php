<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchantPayoutItem extends Model
{
    protected $guarded = [];

    public function payout(): BelongsTo { return $this->belongsTo(MerchantPayout::class, 'payout_id'); }
    public function shipment(): BelongsTo { return $this->belongsTo(Shipment::class); }
}
