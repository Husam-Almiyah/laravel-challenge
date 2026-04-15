<?php

namespace App\Domains\Catalog\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = ['category_id', 'name', 'slug', 'description', 'price', 'is_active'];

    /**
     * Get the category this service belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the packages that include this service.
     */
    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_service');
    }
}
