<?php

declare(strict_types=1);

namespace GiftFactory\SecretSanta\Exception;

use InvalidArgumentException;
use Throwable;

final class NotEnoughPlayers extends InvalidArgumentException
{
    public static function forMinimum(
        int $minimumNumber,
        int $code = 0,
        ?Throwable $previous = null,
    ): self {
        return new self("At least $minimumNumber players required", $code, $previous);
    }

    public static function expectAtLeast(int $minimumNumber, int $numberOfPlayer): void
    {
        if ($numberOfPlayer < $minimumNumber) {
            throw self::forMinimum($minimumNumber);
        }
    }
}
