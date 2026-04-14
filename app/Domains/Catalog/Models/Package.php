<?php

namespace App\Domains\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'description', 'price', 'is_active'];

    /**
     * Get the services included in this package.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'package_service');
    }
}
