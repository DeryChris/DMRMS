<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EducationLevel extends Model
{
    protected $fillable = ['name', 'slug', 'rank', 'min_age', 'max_age'];

    public function requirements(): HasMany
    {
        return $this->hasMany(CorpEducationRequirement::class);
    }
}
