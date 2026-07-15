<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Corp extends Model
{
    protected $fillable = ['name', 'slug', 'sector_id', 'service', 'description', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function educationRequirements(): HasMany
    {
        return $this->hasMany(CorpEducationRequirement::class);
    }
}
