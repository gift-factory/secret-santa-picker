<?php

declare(strict_types=1);

namespace GiftFactory\SecretSanta\Exception;

use InvalidArgumentException;
use Throwable;

final class InvalidPlayer extends InvalidArgumentException
{
    public static function atIndex(int $index, int $code = 0, ?Throwable $previous = null): self
    {
        return new self("Player #$index is not a valid Player instance", $code, $previous);
    }
}
