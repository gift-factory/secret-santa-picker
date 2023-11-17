<?php

declare(strict_types=1);

namespace GiftFactory\SecretSanta\Exception;

use RuntimeException;
use Throwable;

final class MaximumTriesReached extends RuntimeException
{
    public static function after(int $tries, int $code = 0, ?Throwable $previous = null): self
    {
        return new self("Unable to draw after $tries tries, exclusions couldn't be satisfied", $code, $previous);
    }
}
