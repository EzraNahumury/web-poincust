<?php

namespace Tests\Unit;

use App\Domain\Points\PointsCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PointsCalculatorTest extends TestCase
{
    private PointsCalculator $calc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calc = new PointsCalculator(10000, 500);
    }

    #[DataProvider('earnCases')]
    public function test_points_earned_uses_floor(int $eligible, int $expected): void
    {
        $this->assertSame($expected, $this->calc->pointsEarned($eligible));
    }

    public static function earnCases(): array
    {
        // Straight from README §3.1 + §21 acceptance table.
        return [
            'Rp9.000 => 0'        => [9000, 0],
            'Rp10.000 => 1'       => [10000, 1],
            'Rp19.999 => 1'       => [19999, 1],
            'Rp20.000 => 2'       => [20000, 2],
            'Rp75.000 => 7'       => [75000, 7],
            'Rp100.000 => 10'     => [100000, 10],
            'Rp275.000 => 27'     => [275000, 27],
            'Rp1.000.000 => 100'  => [1000000, 100],
            'Rp0 => 0'            => [0, 0],
            'negative => 0'      => [-50000, 0],
            'just under 1 pt'    => [9999, 0],
        ];
    }

    #[DataProvider('redeemCases')]
    public function test_redemption_value(int $points, int $expected): void
    {
        $this->assertSame($expected, $this->calc->redemptionValue($points));
    }

    public static function redeemCases(): array
    {
        return [
            '1 => 500'       => [1, 500],
            '10 => 5.000'    => [10, 5000],
            '20 => 10.000'   => [20, 10000],
            '50 => 25.000'   => [50, 25000],
            '100 => 50.000'  => [100, 50000],
            '200 => 100.000' => [200, 100000],
            '0 => 0'         => [0, 0],
            'negative => 0'  => [-5, 0],
        ];
    }

    public function test_estimated_discount_matches_balance(): void
    {
        $this->assertSame(62500, $this->calc->estimatedDiscount(125));
        $this->assertSame(0, $this->calc->estimatedDiscount(0));
        $this->assertSame(0, $this->calc->estimatedDiscount(-10));
    }

    public function test_custom_configuration_is_respected(): void
    {
        $calc = new PointsCalculator(5000, 1000);
        $this->assertSame(3, $calc->pointsEarned(15999)); // floor(15999/5000)=3
        $this->assertSame(3000, $calc->redemptionValue(3));
        $this->assertSame(5000, $calc->rupiahPerPoint());
        $this->assertSame(1000, $calc->redeemValuePerPoint());
    }

    public function test_invalid_configuration_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PointsCalculator(0, 500);
    }

    public function test_no_floating_point_drift_on_large_amount(): void
    {
        // 1,999,999,990 rupiah — well within integer range, exact floor.
        $this->assertSame(199999, $this->calc->pointsEarned(1999999990));
    }
}
