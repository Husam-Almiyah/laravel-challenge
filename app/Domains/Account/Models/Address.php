<?php

namespace App\Domains\Account\Models;

use App\Models\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'city_id',
        'district',
        'address_details',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the user who owns this address.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the city this address is located in.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
