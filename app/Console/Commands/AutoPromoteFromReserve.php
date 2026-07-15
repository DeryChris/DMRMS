<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\Cycle;
use App\Models\FinalDecision;
use App\Models\ReserveList;
use App\Services\Notification\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoPromoteFromReserve extends Command
{
    protected $signature = 'reserve:promote';
    protected $description = 'Auto-promote reserve candidates to fill vacancies';

    public function handle(NotificationService $notifier): int
    {
        if (!config('recruitment.auto_promote_reserve', false)) {
            $this->info('Auto-promote from reserve is disabled in config.');
            return Command::SUCCESS;
        }

        $cycles = Cycle::where('status', 'active')->get();

        if ($cycles->isEmpty()) {
            $this->info('No active cycles found.');
            return Command::SUCCESS;
        }

        $totalPromoted = 0;

        foreach ($cycles as $cycle) {
            $totalSelected = Application::where('cycle_id', $cycle->id)
                ->whereIn('status', ['selected', 'recruited'])
                ->count();

            $vacancies = $cycle->total_vacancies ?? 0;
            $gap = max(0, $vacancies - $totalSelected);

            if ($gap <= 0) {
                continue;
            }

            $reserveEntries = ReserveList::with('application')
                ->where('cycle_id', $cycle->id)
                ->orderBy('priority_score', 'desc')
                ->limit($gap)
                ->get();

            if ($reserveEntries->isEmpty()) {
                continue;
            }

            $this->info("Cycle {$cycle->name}: {$gap} vacancy(ies), promoting {$reserveEntries->count()} from reserve.");

            DB::transaction(function () use ($reserveEntries, $notifier, $cycle, &$totalPromoted) {
                foreach ($reserveEntries as $entry) {
                    $application = $entry->application;
                    if (!$application) continue;

                    $autoRecruit = config('recruitment.auto_recruit.enabled', false);

                    $application->update([
                        'status' => $autoRecruit ? 'recruited' : 'selected',
                    ]);

                    if ($autoRecruit) {
                        $application->update([
                            'enrolled_at' => Carbon::now(),
                            'training_battalion' => config('recruitment.auto_recruit.default_training_battalion', 'GAF Training Depot'),
                        ]);
                    }

                    FinalDecision::create([
                        'application_id' => $application->id,
                        'decision' => 'selected',
                        'decision_date' => Carbon::now(),
                        'decided_by' => null,
                        'notes' => 'Auto-promoted from reserve list on ' . Carbon::now()->toDateString(),
                    ]);

                    $entry->delete();

                    try {
                        $notifier->finalDecision($application);
                    } catch (\Exception $e) {
                        Log::warning('Promotion notification failed', [
                            'app_id' => $application->id,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    $totalPromoted++;
                }
            });

            $extraCount = Application::where('cycle_id', $cycle->id)
                ->whereIn('status', ['selected', 'recruited'])
                ->count();

            $existingSelected = Application::where('cycle_id', $cycle->id)
                ->whereIn('status', ['selected', 'recruited'])
                ->whereDoesntHave('finalDecision', function ($q) use ($reserveEntries) {
                    $q->whereIn('application_id', $reserveEntries->pluck('application_id'));
                })
                ->get();

            foreach ($existingSelected as $existing) {
                try {
                    $notifier->cohortExpanded($existing->fresh(), $cycle->name, $extraCount);
                } catch (\Exception $e) {
                    Log::warning('Cohort notification failed for applicant', [
                        'app_id' => $existing->id,
                    ]);
                }
            }
        }

        if ($totalPromoted > 0) {
            $this->info("Promoted {$totalPromoted} candidate(s) from reserve list.");
            Log::info("AutoPromoteFromReserve completed", ['promoted' => $totalPromoted]);
        } else {
            $this->info('No reserve candidates to promote.');
        }

        return Command::SUCCESS;
    }
}
