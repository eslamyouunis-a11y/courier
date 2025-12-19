<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchDeposit extends Model
{
    protected $guarded = [];

    /** =============================
     * Statuses
     * ============================= */
    const STATUS_OPEN      = 'open';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_APPROVED  = 'approved';

    /** =============================
     * Relations
     * ============================= */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BranchDepositItem::class, 'deposit_id');
    }
}
