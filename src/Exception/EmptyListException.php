<?php

declare(strict_types=1);

namespace GiftFactory\SecretSanta\Exception;

use RuntimeException;
use Throwable;

final class EmptyListException extends RuntimeException
{
    public function __construct(
        string $message = 'List must contain at least 1 player to pick in it"',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
