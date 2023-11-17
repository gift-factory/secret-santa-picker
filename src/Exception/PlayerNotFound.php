<?php

declare(strict_types=1);

namespace GiftFactory\SecretSanta\Exception;

use InvalidArgumentException;
use Throwable;

final class PlayerNotFound extends InvalidArgumentException
{
    public static function forUserName(string $userName, int $code = 0, ?Throwable $previous = null): self
    {
        return new self("Corrupted list of players found searching for '$userName'", $code, $previous);
    }
}
