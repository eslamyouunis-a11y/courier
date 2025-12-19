<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchDepositItem extends Model
{
    protected $guarded = [];

    public function deposit(): BelongsTo { return $this->belongsTo(BranchDeposit::class, 'deposit_id'); }
    public function shipment(): BelongsTo { return $this->belongsTo(Shipment::class); }
}
