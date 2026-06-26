<?php

namespace App\Console\Commands;

use App\Models\Backup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CreateBackupCommand extends Command
{
    protected $signature = 'dmrms:backup {--type=manual : The backup type (manual/scheduled)}';

    protected $description = 'Create a database backup of the DMRMS system';

    public function handle(): int
    {
        $type = $this->option('type');
        $timestamp = now()->format('Y-m-d-His');
        $filename = "dmrms-backup-{$timestamp}.sql";
        $filepath = "backups/{$filename}";

        $this->info('Creating database backup...');

        try {
            $connection = config('database.default');
            $config = config("database.connections.{$connection}");

            if ($connection !== 'pgsql') {
                throw new \RuntimeException("Unsupported database connection: {$connection}");
            }

            $host = $config['host'] ?? '127.0.0.1';
            $port = $config['port'] ?? '5432';
            $database = $config['database'];
            $username = $config['username'] ?? 'postgres';
            $password = $config['password'] ?? '';

            $command = "PGPASSWORD={$password} pg_dump -h {$host} -p {$port} -U {$username} -d {$database} --no-owner --clean 2>&1";

            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new \RuntimeException('pg_dump failed: ' . implode("\n", $output));
            }

            $sql = implode("\n", $output);
            Storage::disk('local')->put($filepath, $sql);

            $size = Storage::disk('local')->size($filepath);

            Backup::create([
                'filename' => $filename,
                'filepath' => $filepath,
                'size_bytes' => $size,
                'type' => $type,
                'status' => 'completed',
                'created_by' => 1,
            ]);

            $this->info("Backup created: {$filename} (" . round($size / 1024, 2) . " KB)");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Backup failed: {$e->getMessage()}");

            Backup::create([
                'filename' => $filename,
                'filepath' => $filepath,
                'size_bytes' => 0,
                'type' => $type,
                'status' => 'failed',
                'notes' => $e->getMessage(),
                'created_by' => 1,
            ]);

            return Command::FAILURE;
        }
    }
}
