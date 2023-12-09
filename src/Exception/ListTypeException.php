<?php

declare(strict_types=1);

namespace GiftFactory\SecretSanta\Exception;

use RuntimeException;
use Throwable;

final class ListTypeException extends RuntimeException
{
    public static function forTypes(string $name, array $types, ?Throwable $previous = null): self
    {
        return new self(
            "$name must be a list of ".implode('|', $types),
            previous: $previous,
        );
    }

    public static function forCount(string $name, int $count, ?Throwable $previous = null): self
    {
        return new self(
            "$name must be a list of $count elements",
            previous: $previous,
        );
    }

    public static function assertItemType(string $name, mixed $value, array $types): void
    {
        if (!self::isListOf($value, $types)) {
            throw self::forTypes($name, $types);
        }
    }

    public static function assertCount(string $name, mixed $value, int $count): void
    {
        if (!is_array($value) || !array_is_list($value) || count($value) !== $count) {
            throw self::forCount($name, $count);
        }
    }

    public static function assertCountAndItemType(string $name, mixed $value, int $count, array $types): void
    {
        self::assertCount($name, $value, $count);
        self::assertItemType($name, $value, $types);
    }

    private static function isListOf(mixed $value, array $types): bool
    {
        if (!is_array($value) || !array_is_list($value)) {
            return false;
        }

        $types = array_map(strtolower(...), $types);

        foreach ($value as $item) {
            $type = strtolower(gettype($item));

            if (in_array($type, $types, true)) {
                continue;
            }

            if ($type !== 'object' || !self::isAmong($item, $types)) {
                return false;
            }
        }

        return true;
    }

    private static function isAmong(object $value, array $types): bool
    {
        foreach ($types as $type) {
            if (is_a($value, $type)) {
                return true;
            }
        }

        return false;
    }
}
