<?php

namespace App\Domains\Catalog\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = ['name', 'slug', 'description', 'price', 'discount_percentage', 'is_active'];

    /**
     * Get the services included in this package.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'package_service');
    }

    /**
     * Get the category that the package belongs to.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
