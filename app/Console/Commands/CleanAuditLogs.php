<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanAuditLogs extends Command
{
    protected $signature = 'audit:clean {--days=90 : Delete audit logs older than this many days}';

    protected $description = 'Clean audit logs older than specified days';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = Carbon::now()->subDays($days);

        $count = AuditLog::where('created_at', '<', $cutoff)->delete();

        $this->info("Cleaned {$count} audit log(s)");

        return Command::SUCCESS;
    }
}
