<?php

namespace App\Domains\Catalog\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = ['name', 'slug', 'priority', 'is_active'];

    /**
     * Get the services belonging to this category.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}
