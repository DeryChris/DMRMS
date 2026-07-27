<?php

namespace App\Jobs;

use App\Models\Applicant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAdminBroadcastEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Applicant $applicant,
        public string $subject,
        public string $messageBody,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $fromAddress = config('mail.from.address', 'amoaheugene23@gmail.com');
        $fromName = config('mail.from.name', 'Ghana Armed Forces');

        try {
            Mail::send('emails.admin-broadcast', [
                'applicant'   => $this->applicant,
                'subject'     => $this->subject,
                'messageBody' => $this->messageBody,
            ], function ($mail) use ($fromAddress, $fromName) {
                $mail->to($this->applicant->email, $this->applicant->name)
                     ->subject($this->subject)
                     ->from($fromAddress, $fromName);
            });

            Log::info('Admin broadcast email sent', [
                'applicant' => $this->applicant->email,
                'subject'   => $this->subject,
            ]);
        } catch (\Exception $e) {
            Log::error("Admin broadcast email failed to {$this->applicant->email}: " . $e->getMessage());

            if ($this->attempts() >= $this->tries) {
                Log::warning("Admin broadcast email exhausted retries for {$this->applicant->email}");
            } else {
                throw $e; // re-throw to trigger retry
            }
        }
    }
}
