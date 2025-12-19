<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchTransferItem extends Model
{
    protected $guarded = [];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(BranchTransfer::class);
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
