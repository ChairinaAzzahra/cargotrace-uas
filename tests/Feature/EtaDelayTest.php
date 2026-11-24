<?php

namespace Tests\Feature;

use App\Services\EtaService;
use Carbon\Carbon;
use Tests\TestCase;

class EtaDelayTest extends TestCase
{
    protected EtaService $etaService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->etaService = app(EtaService::class);
    }

    public function test_on_time_when_estimated_arrival_is_before_or_equal_to_expected(): void
    {
        $base = Carbon::now()->addDays(5);
        $expected = $base->copy();
        $estimated = $base->copy();

        $result = $this->etaService->calculateDelay($expected, $estimated);

        $this->assertFalse($result['is_delayed']);
        $this->assertEquals(0, $result['delay_hours']);
        $this->assertEquals('NONE', $result['severity']);
    }

    public function test_low_severity_delay_under_six_hours(): void
    {
        $expected = Carbon::now()->addDays(5);
        $estimated = (clone $expected)->addHours(4); // 4 hours late

        $result = $this->etaService->calculateDelay($expected, $estimated);

        $this->assertTrue($result['is_delayed']);
        $this->assertEquals(4, $result['delay_hours']);
        $this->assertEquals('LOW', $result['severity']);
    }

    public function test_medium_severity_delay_between_six_and_twenty_four_hours(): void
    {
        $expected = Carbon::now()->addDays(5);
        $estimated = (clone $expected)->addHours(18); // 18 hours late

        $result = $this->etaService->calculateDelay($expected, $estimated);

        $this->assertTrue($result['is_delayed']);
        $this->assertEquals(18, $result['delay_hours']);
        $this->assertEquals('MEDIUM', $result['severity']);
    }

    public function test_high_severity_delay_above_twenty_four_hours(): void
    {
        $expected = Carbon::now()->addDays(5);
        $estimated = (clone $expected)->addHours(36); // 36 hours late

        $result = $this->etaService->calculateDelay($expected, $estimated);

        $this->assertTrue($result['is_delayed']);
        $this->assertEquals(36, $result['delay_hours']);
        $this->assertEquals('HIGH', $result['severity']);
    }
}
