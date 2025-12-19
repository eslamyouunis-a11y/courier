<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchantPayout extends Model
{
    protected $guarded = [];

    /* =============================
     | Statuses
     ============================= */
    public const STATUS_OPEN = 'open';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';

    /* =============================
     | Relations
     ============================= */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }
}
