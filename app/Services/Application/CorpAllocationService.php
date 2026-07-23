<?php

namespace App\Services\Application;

use App\Models\Application;
use App\Models\Corp;
use App\Models\ApplicantCorpSelection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CorpAllocationService
{
    public function __construct(
        protected CorpMatchingService $corpMatchingService,
    ) {}

    public function allocate(int $cycleId): array
    {
        $applications = Application::where('cycle_id', $cycleId)
            ->where('status', 'shortlisted')
            ->where('allocation_status', 'pending')
            ->orderBy('ai_ranking_score', 'desc')
            ->orderBy('submitted_at')
            ->get();

        if ($applications->isEmpty()) {
            return ['allocated' => 0, 'unallocated' => 0, 'skipped' => 0];
        }

        $allocated = 0;
        $unallocated = 0;
        $skipped = 0;

        DB::transaction(function () use ($applications, &$allocated, &$unallocated, &$skipped) {
            foreach ($applications as $application) {
                $corpId = $this->resolveAllocation($application);

                if ($corpId === null) {
                    $unallocated++;
                    continue;
                }

                $corp = Corp::find($corpId);
                if (!$corp) {
                    $skipped++;
                    continue;
                }

                if (!$this->hasCapacity($corp, $application)) {
                    $application->updateQuietly([
                        'allocation_status' => 'unallocated',
                    ]);
                    $unallocated++;
                    continue;
                }

                $application->updateQuietly([
                    'allocated_corp_id' => $corpId,
                    'allocation_status' => 'allocated',
                ]);

                $this->decrementCapacity($corp);

                $allocated++;
            }
        });

        return [
            'allocated' => $allocated,
            'unallocated' => $unallocated,
            'skipped' => $skipped,
        ];
    }

    public function allocateSingle(Application $application): ?int
    {
        if ($application->allocation_status === 'allocated') {
            return $application->allocated_corp_id;
        }

        return DB::transaction(function () use ($application) {
            $corpId = $this->resolveAllocation($application);

            if ($corpId === null) {
                $application->updateQuietly(['allocation_status' => 'unallocated']);
                return null;
            }

            $corp = Corp::find($corpId);
            if (!$corp || !$this->hasCapacity($corp, $application)) {
                $application->updateQuietly(['allocation_status' => 'unallocated']);
                return null;
            }

            $application->updateQuietly([
                'allocated_corp_id' => $corpId,
                'allocation_status' => 'allocated',
            ]);

            $this->decrementCapacity($corp);

            return $corpId;
        });
    }

    protected function resolveAllocation(Application $application): ?int
    {
        $selections = ApplicantCorpSelection::where('application_id', $application->id)
            ->orderBy('priority')
            ->get();

        $eligibleCorpIds = $this->corpMatchingService->getEligibleCorpIds($application);

        if ($selections->isEmpty()) {
            return $this->fallbackAllocation($application, $eligibleCorpIds);
        }

        foreach ($selections as $selection) {
            if (!in_array($selection->corp_id, $eligibleCorpIds)) {
                continue;
            }

            $corp = Corp::find($selection->corp_id);
            if ($corp && $this->hasCapacity($corp, $application)) {
                return $corp->id;
            }
        }

        return $this->fallbackAllocation($application, $eligibleCorpIds);
    }

    protected function fallbackAllocation(Application $application, array $eligibleCorpIds): ?int
    {
        if (empty($eligibleCorpIds)) {
            return null;
        }

        $corps = Corp::whereIn('id', $eligibleCorpIds)
            ->where('is_active', true)
            ->orderBy('max_capacity', 'desc')
            ->get();

        foreach ($corps as $corp) {
            if ($this->hasCapacity($corp, $application)) {
                return $corp->id;
            }
        }

        return null;
    }

    protected function hasCapacity(Corp $corp, Application $application): bool
    {
        if ($corp->max_capacity === null) {
            return true;
        }

        $currentCount = Application::where('allocated_corp_id', $corp->id)
            ->whereIn('allocation_status', ['allocated'])
            ->count();

        return $currentCount < $corp->max_capacity;
    }

    protected function decrementCapacity(Corp $corp): void
    {
        // capacity is computed dynamically from counts, not decremented
    }

    public function getAllocationStats(int $cycleId): array
    {
        $total = Application::where('cycle_id', $cycleId)
            ->where('status', 'shortlisted')
            ->count();

        $allocated = Application::where('cycle_id', $cycleId)
            ->where('allocation_status', 'allocated')
            ->count();

        $unallocated = Application::where('cycle_id', $cycleId)
            ->where('allocation_status', 'unallocated')
            ->count();

        $pending = Application::where('cycle_id', $cycleId)
            ->where('status', 'shortlisted')
            ->where('allocation_status', 'pending')
            ->count();

        $byCorp = Application::where('cycle_id', $cycleId)
            ->where('allocation_status', 'allocated')
            ->select('allocated_corp_id', DB::raw('count(*) as total'))
            ->groupBy('allocated_corp_id')
            ->get()
            ->mapWithKeys(fn($row) => [
                $row->allocated_corp_id => [
                    'corp' => Corp::find($row->allocated_corp_id)?->name ?? 'Unknown',
                    'count' => $row->total,
                ],
            ]);

        return [
            'total' => $total,
            'allocated' => $allocated,
            'unallocated' => $unallocated,
            'pending' => $pending,
            'by_corp' => $byCorp,
        ];
    }

    public function resetAllocation(int $applicationId): void
    {
        Application::where('id', $applicationId)->update([
            'allocated_corp_id' => null,
            'allocation_status' => 'pending',
        ]);
    }

    public function resetCycleAllocations(int $cycleId): void
    {
        Application::where('cycle_id', $cycleId)
            ->whereIn('allocation_status', ['allocated', 'unallocated'])
            ->update([
                'allocated_corp_id' => null,
                'allocation_status' => 'pending',
            ]);
    }
}
