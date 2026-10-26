<?php

namespace App\Domain\Points;

class RedemptionExceedsBillException extends PointsException
{
    public static function make(int $discount, int $bill): self
    {
        return new self("Nilai diskon penukaran (Rp{$discount}) melebihi tagihan yang diperbolehkan (Rp{$bill}).");
    }
}
