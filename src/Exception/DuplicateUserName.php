<?php

declare(strict_types=1);

namespace GiftFactory\SecretSanta\Exception;

use InvalidArgumentException;
use Throwable;

final class DuplicateUserName extends InvalidArgumentException
{
    public static function atIndexes(
        int $previousIndex,
        int $index,
        string $userName,
        int $code = 0,
        ?Throwable $previous = null,
    ): self {
        return new self(
            "Players #$previousIndex and #$index  have the same user name: $userName",
            $code,
            $previous,
        );
    }
}
