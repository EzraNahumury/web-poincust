<?php

namespace App\Domain\Points;

use App\Models\Setting;
use Throwable;

/**
 * Resolves the active point-program parameters (settings table first, then
 * config defaults) and builds a configured PointsCalculator. Keeping this in
 * one place guarantees frontend estimates and backend totals use identical
 * numbers.
 */
class PointsConfig
{
    public function rupiahPerPoint(): int
    {
        return $this->resolve(
            config('points.keys.rupiah_per_point'),
            (int) config('points.rupiah_per_point', 10000),
        );
    }

    public function redeemValuePerPoint(): int
    {
        return $this->resolve(
            config('points.keys.redeem_value'),
            (int) config('points.redeem_value', 500),
        );
    }

    public function calculator(): PointsCalculator
    {
        return new PointsCalculator($this->rupiahPerPoint(), $this->redeemValuePerPoint());
    }

    private function resolve(string $settingKey, int $default): int
    {
        try {
            $value = Setting::get($settingKey);

            return $value === null ? $default : max(1, (int) $value);
        } catch (Throwable) {
            // Settings table not available yet (e.g. pre-migration / console).
            return $default;
        }
    }
}
