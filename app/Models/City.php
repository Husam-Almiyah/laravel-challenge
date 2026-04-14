<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = ['name', 'slug', 'is_active'];

    /**
     * Get the users residing in this city.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
