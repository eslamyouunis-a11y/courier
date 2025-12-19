<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Courier extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'phone',
        'email',
        'id_number',
        'branch_id',
        'commission_delivered',
        'commission_returned_sender',
        'commission_returned_paid',
        'is_active',
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'commission_delivered' => 'float',
        'commission_returned_sender' => 'float',
        'commission_returned_paid' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($courier) {
            $latest = static::latest('id')->first();
            $nextId = $latest ? $latest->id + 1 : 1;
            $courier->code = 'CR-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        });

        // إنشاء محفظة تلقائية عند إنشاء المندوب
        static::created(function ($courier) {
            $courier->wallet()->create([
                'balance' => 0,
            ]);
        });
    }

    // ==============================
    // العلاقات (Relationships)
    // ==============================

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function wallet(): MorphOne
    {
        return $this->morphOne(Wallet::class, 'owner');
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    // العلاقة الضرورية لملف التصفيات
    public function handovers(): HasMany
    {
        return $this->hasMany(CourierHandover::class);
    }
}
