<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Branch extends Model
{
    protected $fillable = ['code', 'name', 'phone', 'address', 'governorate_id', 'wallet_balance', 'manager_name', 'manager_phone', 'is_active'];

    // توليد الكود تلقائياً
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($branch) {
            $latestBranch = static::latest('id')->first();
            $nextId = $latestBranch ? $latestBranch->id + 1 : 1;
            $branch->code = 'BR-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
        });
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }
}
