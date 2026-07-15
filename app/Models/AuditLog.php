<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'id',
        'user_id',
        'user_type',
        'action',
        'details',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $guarded = [];

    public $timestamps = true;

    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class, 'user_id');
    }

    public function administrator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'user_login' => 'User Login',
            'user_logout' => 'User Logout',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
            'user_deleted' => 'User Deleted',
            'applicant_created' => 'Applicant Registered',
            'applicant_updated' => 'Applicant Updated',
            'applicant_deleted' => 'Applicant Deleted',
            'applicant_status_changed' => 'Applicant Status Changed',
            'application_submitted' => 'Application Submitted',
            'application_updated' => 'Application Updated',
            'application_withdrawn' => 'Application Withdrawn',
            'document_uploaded' => 'Document Uploaded',
            'document_verified' => 'Document Verified',
            'document_rejected' => 'Document Rejected',
            'voucher_purchased' => 'Voucher Purchased',
            'voucher_used' => 'Voucher Used',
            'cycle_created' => 'Cycle Created',
            'cycle_updated' => 'Cycle Updated',
            'cycle_published' => 'Cycle Published',
            'cycle_closed' => 'Cycle Closed',
            'cycle_archived' => 'Cycle Archived',
            'appointment_scheduled' => 'Appointment Scheduled',
            'appointment_rescheduled' => 'Appointment Rescheduled',
            'appointment_cancelled' => 'Appointment Cancelled',
            'appointment_checked_in' => 'Applicant Checked In',
            'screening_completed' => 'Screening Completed',
            'screening_updated' => 'Screening Updated',
            'password_changed' => 'Password Changed',
            'settings_updated' => 'Settings Updated',
            'backup_created' => 'Backup Created',
            'backup_deleted' => 'Backup Deleted',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    public function getDetailsSummaryAttribute(): string
    {
        $details = $this->details;
        if (empty($details)) {
            return '—';
        }
        if (is_string($details)) {
            return $details;
        }

        $checkLabels = [
            'age' => 'Age',
            'nationality' => 'Nationality',
            'education' => 'Education',
            'height' => 'Height',
            'marital_status' => 'Marital',
            'criminal_record' => 'Criminal',
            'documents' => 'Documents',
        ];

        $parts = [];

        foreach ($details as $key => $value) {
            if (is_null($value) || $value === '') {
                continue;
            }

            // Handle eligibility checks (nested array from EligibilityEngine)
            if ($key === 'checks' && is_array($value)) {
                $checkParts = [];
                foreach ($value as $checkKey => $checkVal) {
                    $label = $checkLabels[$checkKey] ?? ucfirst(str_replace('_', ' ', $checkKey));
                    $passed = $checkVal['passed'] ?? $checkVal;
                    if (is_bool($passed)) {
                        $checkParts[] = ($passed ? '✓' : '✗') . ' ' . $label;
                    }
                }
                if (!empty($checkParts)) {
                    $parts[] = implode(', ', $checkParts);
                }
                continue;
            }

            // Handle rejection_reasons as a readable list
            if ($key === 'rejection_reasons' && is_array($value)) {
                $reasons = array_filter($value, fn($v) => is_string($v) && !empty($v));
                if (!empty($reasons)) {
                    $parts[] = 'Rejected: ' . implode('; ', $reasons);
                }
                continue;
            }

            // Skip verbose/internal fields
            if (in_array($key, ['refreshed_by', 'returned_count'], true)) {
                continue;
            }

            // Format boolean values
            if (is_bool($value)) {
                $value = $value ? 'Yes' : 'No';
            } elseif (is_array($value)) {
                $value = '[' . count($value) . ' items]';
            }

            // Clean up common keys for display
            $label = match ($key) {
                'overall_status' => 'Status',
                'previous_status' => 'Previous',
                'to_status' => 'To',
                'from_status' => 'From',
                'applicant_name' => 'Applicant',
                'gaf_id' => 'GAF ID',
                'application_id' => 'Application',
                default => ucfirst(str_replace(['_id', '_'], [' ID', ' '], $key)),
            };

            $parts[] = "$label: $value";
        }

        return implode(', ', $parts);
    }
}
