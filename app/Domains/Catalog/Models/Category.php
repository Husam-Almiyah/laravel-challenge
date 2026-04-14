<?php

namespace App\Domains\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'priority', 'is_active'];

    /**
     * Get the services belonging to this category.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}
