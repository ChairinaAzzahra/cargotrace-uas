<?php

namespace App\Services;

use App\Models\Shipment;
use Carbon\Carbon;

class EtaService
{
    /**
     * Compare estimated arrival with expected arrival and calculate delay deterministically.
     * Rules defined in UAS Brief slide 8:
     * - If estimated arrival <= expected arrival: ON_TIME, delay_duration = 0, severity = NONE.
     * - If estimated arrival > expected arrival: DELAYED, calculate difference in hours.
     * - Thresholds: 
     *     0 < delay <= low_threshold (e.g. 6h) => LOW
     *     low_threshold < delay <= medium_threshold (e.g. 24h) => MEDIUM
     *     delay > medium_threshold => HIGH
     *
     * @return array{is_delayed: bool, delay_hours: int, severity: string, reason: string}
     */
    public function calculateDelay(mixed $expectedArrival, mixed $estimatedArrival): array
    {
        if (!$estimatedArrival) {
            return [
                'is_delayed' => false,
                'delay_hours' => 0,
                'severity' => 'NONE',
                'reason' => 'Estimated arrival time not set; assumed on schedule.',
            ];
        }

        $expected = Carbon::parse($expectedArrival);
        $estimated = Carbon::parse($estimatedArrival);

        if ($estimated->lessThanOrEqualTo($expected)) {
            return [
                'is_delayed' => false,
                'delay_hours' => 0,
                'severity' => 'NONE',
                'reason' => sprintf(
                    'Estimated arrival (%s) is on or before expected arrival (%s).',
                    $estimated->toIso8601String(),
                    $expected->toIso8601String()
                ),
            ];
        }

        $delayHours = (int) ceil($expected->diffInMinutes($estimated) / 60);

        $lowThreshold = config('cargotrace.delay_thresholds.low', 6);
        $mediumThreshold = config('cargotrace.delay_thresholds.medium', 24);

        if ($delayHours <= $lowThreshold) {
            $severity = 'LOW';
        } elseif ($delayHours <= $mediumThreshold) {
            $severity = 'MEDIUM';
        } else {
            $severity = 'HIGH';
        }

        return [
            'is_delayed' => true,
            'delay_hours' => $delayHours,
            'severity' => $severity,
            'reason' => sprintf(
                'Estimated arrival (%s) is %d hour(s) past expected arrival (%s). Classified as %s severity (Low <= %dh, Med <= %dh, High > %dh).',
                $estimated->toIso8601String(),
                $delayHours,
                $expected->toIso8601String(),
                $severity,
                $lowThreshold,
                $mediumThreshold,
                $mediumThreshold
            ),
        ];
    }

    /**
     * Recalculate and update delay attributes on a shipment.
     */
    public function updateShipmentDelay(Shipment $shipment): void
    {
        $result = $this->calculateDelay($shipment->expected_arrival_at, $shipment->estimated_arrival_at);

        $shipment->update([
            'delay_status' => $result['is_delayed'] ? 'DELAYED' : 'ON_TIME',
            'delay_duration_hours' => $result['delay_hours'],
            'delay_severity' => $result['severity'],
        ]);
    }
}
