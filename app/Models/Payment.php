<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'stall_rental_id',
        'business_id',
        'volantes_id',
        'receipt',
        'date',
        'amount',
        'reference_number',
        'status',
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function volantes(): HasOne
    {
        return $this->hasOne(Volante::class);
    }

    public function business(): HasOne
    {
        return $this->hasOne(Business::class);
    }
    
    public function stallRentals(): HasOne
    {
        return $this->hasOne(StallRental::class);
    }
}
