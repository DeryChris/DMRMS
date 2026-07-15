<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicantCorpSelection extends Model
{
    protected $table = 'applicant_corp_selections';

    protected $fillable = ['application_id', 'corp_id', 'priority'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function corp(): BelongsTo
    {
        return $this->belongsTo(Corp::class);
    }
}
