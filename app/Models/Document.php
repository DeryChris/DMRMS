<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    protected $table = 'documents';

    protected $fillable = [
        'application_id',
        'document_type',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'upload_date',
        'verification_status',
        'is_draft',
        'finalized_at',
        'verified_by',
        'verified_at',
        'fraud_risk_score',
        'fraud_flags',
        'ai_verified',
        'ai_extracted_data',
        'ai_confidence',
        'extracted_data',
        'cross_reference_results',
        'ai_verified_at',
        'ai_verification_attempts',
    ];

    protected $appends = ['file_url', 'view_url'];

    protected function casts(): array
    {
        return [
            'fraud_flags' => 'array',
            'ai_extracted_data' => 'array',
            'extracted_data' => 'array',
            'cross_reference_results' => 'array',
            'ai_verified' => 'boolean',
            'is_draft' => 'boolean',
            'ai_verified_at' => 'datetime',
            'upload_date' => 'datetime',
            'verified_at' => 'datetime',
            'finalized_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function getViewUrlAttribute(): string
    {
        return route('documents.view', $this->id);
    }
}
