<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchTransfer extends Model
{
    protected $guarded = [];

    const STATUS_PENDING  = 'pending';   // لسه موصلش
    const STATUS_ACCEPTED = 'accepted';  // الفرع استلم
    const STATUS_CANCELED = 'canceled';  // اتلغى من المصدر

    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BranchTransferItem::class);
    }
}
