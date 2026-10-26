<?php

namespace App\Domain\Points;

class TransactionAlreadyReversedException extends PointsException
{
    public static function make(string $number): self
    {
        return new self("Transaksi {$number} sudah dibatalkan sebelumnya.");
    }
}
