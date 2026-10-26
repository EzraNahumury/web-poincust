<?php

namespace App\Domain\Points;

class InsufficientPointsException extends PointsException
{
    public static function make(int $requested, int $available): self
    {
        return new self("Poin yang ditukarkan ({$requested}) melebihi saldo tersedia ({$available}).");
    }
}
