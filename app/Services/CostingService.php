<?php

namespace App\Services;

use App\Models\CostRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CostingService
{
    /**
     * Record estimated and actual cost for a costable entity (WorkOrder or Project).
     */
    public function recordCost(Model $costable, string $costType, float $estimatedCost = 0.0, float $actualCost = 0.0, ?string $notes = null): CostRecord
    {
        return CostRecord::create([
            'costable_type' => get_class($costable),
            'costable_id' => $costable->getKey(),
            'cost_type' => $costType,
            'estimated_cost' => $estimatedCost,
            'actual_cost' => $actualCost,
            'notes' => $notes,
        ]);
    }

    /**
     * Get costing summary breakdown for a costable entity.
     */
    public function getCostSummary(Model $costable): array
    {
        $records = CostRecord::where('costable_type', get_class($costable))
            ->where('costable_id', $costable->getKey())
            ->get();

        $totalEstimated = $records->sum('estimated_cost');
        $totalActual = $records->sum('actual_cost');
        $variance = $totalActual - $totalEstimated;

        $byType = [];
        foreach ($records as $r) {
            if (!isset($byType[$r->cost_type])) {
                $byType[$r->cost_type] = ['estimated' => 0.0, 'actual' => 0.0];
            }
            $byType[$r->cost_type]['estimated'] += $r->estimated_cost;
            $byType[$r->cost_type]['actual'] += $r->actual_cost;
        }

        return [
            'total_estimated' => round($totalEstimated, 2),
            'total_actual' => round($totalActual, 2),
            'variance' => round($variance, 2),
            'breakdown' => $byType,
        ];
    }
}
