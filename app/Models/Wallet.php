<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_type',
        'owner_id',
        'currency',
        'balance',
    ];

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    // 👇 هذه العلاقة كانت ناقصة وهي سبب الخطأ
    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
