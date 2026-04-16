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
     * The "booted" method of the model.
     *
     * Set up event listeners to clean up pivot records when package is deleted.
     */
    protected static function booted(): void
    {
        // When soft deleting, delete pivot records
        static::deleting(function ($package) {
            if ($package->isForceDeleting()) {
                // Force delete: pivot will be deleted by cascade
                return;
            }

            // Soft delete: clean up pivot records
            $package->services()->detach();
        });
    }

    /**
     * Get the services included in this package.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'package_service');
    }
}
