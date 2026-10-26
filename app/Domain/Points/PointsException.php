<?php

namespace App\Domain\Points;

use RuntimeException;

/** Base for all point/transaction business-rule violations. */
class PointsException extends RuntimeException
{
}
