<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BackupController extends Controller
{
    public function index(): View
    {
        $backups = Backup::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $storageUsed = Backup::where('status', 'completed')->sum('size_bytes');
        $totalBackups = Backup::count();
        $lastBackup = Backup::where('status', 'completed')->latest()->first();
        $todayBackups = Backup::where('status', 'completed')->whereDate('created_at', today())->count();

        return view('admin.backups.index', compact('backups', 'storageUsed', 'totalBackups', 'lastBackup', 'todayBackups'));
    }

    public function create(): RedirectResponse
    {
        $exitCode = Artisan::call('dmrms:backup');

        if ($exitCode === 0) {
            return redirect()->route('admin.backups.index')->with('success', 'Backup created successfully.');
        }

        return redirect()->route('admin.backups.index')->with('error', 'Backup failed. Check logs for details.');
    }

    public function download(Backup $backup)
    {
        if (!Storage::disk('local')->exists($backup->filepath)) {
            return redirect()->route('admin.backups.index')->with('error', 'Backup file not found.');
        }

        return Storage::disk('local')->download($backup->filepath, $backup->filename);
    }

    public function destroy(Backup $backup): RedirectResponse
    {
        if (Storage::disk('local')->exists($backup->filepath)) {
            Storage::disk('local')->delete($backup->filepath);
        }

        $backup->delete();

        return redirect()->route('admin.backups.index')->with('success', 'Backup deleted successfully.');
    }
}
