<?php

declare(strict_types=1);

namespace GiftFactory\SecretSanta\Exception;

use InvalidArgumentException;
use Throwable;

final class UserNameNotFound extends InvalidArgumentException
{
    public static function for(string $userName, int $code = 0, ?Throwable $previous = null): self
    {
        return new self("User '$userName' not found", $code, $previous);
    }
}
