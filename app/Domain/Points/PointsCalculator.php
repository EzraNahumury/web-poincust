<?php

namespace App\Domain\Points;

use InvalidArgumentException;

/**
 * Single source of truth for all point arithmetic.
 *
 * Business rules (README §3):
 *   - Earn: floor(eligible_amount / rupiah_per_point). Default Rp10.000 => 1 point.
 *   - Redeem value: points * redeem_value_per_point. Default Rp500 per point.
 *
 * ALL values are integer rupiah / integer points. No floating point is ever
 * used for money, so rounding is exact and deterministic.
 */
class PointsCalculator
{
    public function __construct(
        private readonly int $rupiahPerPoint = 10000,
        private readonly int $redeemValuePerPoint = 500,
    ) {
        if ($this->rupiahPerPoint < 1) {
            throw new InvalidArgumentException('rupiahPerPoint must be >= 1');
        }
        if ($this->redeemValuePerPoint < 1) {
            throw new InvalidArgumentException('redeemValuePerPoint must be >= 1');
        }
    }

    /**
     * Points earned from an eligible spend amount.
     * Always rounds DOWN (floor). Non-positive amounts earn 0.
     */
    public function pointsEarned(int $eligibleAmount): int
    {
        if ($eligibleAmount <= 0) {
            return 0;
        }

        // intdiv on non-negative integers == floor division. Exact, no floats.
        return intdiv($eligibleAmount, $this->rupiahPerPoint);
    }

    /**
     * Rupiah discount value of a number of points.
     */
    public function redemptionValue(int $points): int
    {
        if ($points <= 0) {
            return 0;
        }

        return $points * $this->redeemValuePerPoint;
    }

    /**
     * Estimated discount a customer could get from their current balance.
     * This is NOT withdrawable cash — purely an indicative figure.
     */
    public function estimatedDiscount(int $balance): int
    {
        return $this->redemptionValue(max(0, $balance));
    }

    public function rupiahPerPoint(): int
    {
        return $this->rupiahPerPoint;
    }

    public function redeemValuePerPoint(): int
    {
        return $this->redeemValuePerPoint;
    }
}
