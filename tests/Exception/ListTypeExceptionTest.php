<?php

declare(strict_types=1);

namespace Tests\GiftFactory\SecretSanta\Exception;

use ArrayObject;
use GiftFactory\SecretSanta\Exception\ListTypeException;
use GiftFactory\SecretSanta\Player;
use GiftFactory\SecretSanta\PlayerList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function count;
use function is_array;

#[CoversClass(ListTypeException::class)]
final class ListTypeExceptionTest extends TestCase
{
    public function testMessage(): void
    {
        $exception = ListTypeException::forTypes('exclusions', ['string', Player::class]);

        self::assertSame(
            'exclusions must be a list of string|GiftFactory\SecretSanta\Player',
            $exception->getMessage(),
        );
    }

    #[DataProvider('getAssertItemTypeDataProvider')]
    public function testAssertItemType(
        bool $allowed,
        string $name,
        mixed $value,
        array $types,
    ): void {
        if (!$allowed) {
            self::expectExceptionObject(
                ListTypeException::forTypes($name, $types),
            );
        }

        ListTypeException::assertItemType($name, $value, $types);

        self::assertTrue($allowed);
    }

    #[DataProvider('getAssertItemTypeDataProvider')]
    public function testAssertItemTypeForKey(
        bool $allowed,
        string $name,
        mixed $value,
        array $types,
    ): void {
        if (!$allowed) {
            self::expectExceptionObject(
                ListTypeException::forTypes("$name\[foobar]", $types),
            );
        }

        ListTypeException::assertItemTypeForKey($name, ['foobar' => $value], 'foobar', $types);

        self::assertTrue($allowed);
    }

    public static function getAssertItemTypeDataProvider(): array
    {
        $cases = [
            [true, 'wishes', ['ab', 'cd'], ['string']],
            [false, 'wishes', ['ab', 'cd', null], ['string']],
            [false, 'wishes', new ArrayObject(['ab']), ['string']],
            [false, 'wishes', ['ab' => 'cd'], ['string']],
            [true, 'wishes', ['ab', 'cd', null], ['string', 'null']],
            [true, 'exclusions', ['ab', 'cd', new Player('cd')], ['string', Player::class]],
            [false, 'exclusions', ['ab', 'cd', new Player('cd'), new PlayerList()], ['string', Player::class]],
        ];

        return array_combine(array_map(
            static fn (array $parameters) => json_encode($parameters[2]).' '.json_encode($parameters[3]),
            $cases,
        ), $cases);
    }

    #[DataProvider('getAssertCountDataProvider')]
    public function testAssertCount(
        bool $allowed,
        string $name,
        mixed $value,
        int $count,
    ): void {
        if (!$allowed) {
            self::expectExceptionObject(
                ListTypeException::forCount($name, $count),
            );
        }

        ListTypeException::assertCount($name, $value, $count);

        self::assertTrue($allowed);
    }

    public static function getAssertCountDataProvider(): array
    {
        $cases = [
            [true, 'wishes', ['ab', 'cd'], 2],
            [false, 'wishes', ['ab', 'cd', null], 2],
            [false, 'wishes', ['ab' => 'cd'], 1],
        ];

        return array_combine(array_map(
            static fn (array $parameters) => json_encode($parameters[2]).' '.$parameters[3],
            $cases,
        ), $cases);
    }

    #[DataProvider('getAssertCountAndItemTypeDataProvider')]
    public function testAssertCountAndItemType(
        bool $allowedCount,
        bool $allowedType,
        string $name,
        mixed $value,
        int $count,
        array $types,
    ): void {
        if (!$allowedCount) {
            self::expectExceptionObject(
                ListTypeException::forCount($name, $count),
            );
        } elseif (!$allowedType) {
            self::expectExceptionObject(
                ListTypeException::forTypes($name, $types),
            );
        }

        ListTypeException::assertCountAndItemType($name, $value, $count, $types);

        self::assertTrue($allowedType && $allowedCount);
    }

    public static function getAssertCountAndItemTypeDataProvider(): array
    {
        $cases = [];

        foreach (self::getAssertItemTypeDataProvider() as $key => [$allowed, $name, $value, $types]) {
            $count = count((array) $value);
            $cases["$key - count ok"] = [is_array($value) && array_is_list($value), $allowed, $name, $value, $count, $types];
            $cases["$key - wrong count"] = [false, $allowed, $name, $value, $count + mt_rand(1, 4) * (mt_rand(0, 1) ? 1 : -1), $types];
        }

        return $cases;
    }

    public function testAssertItemTypeForMissingKey(): void
    {
        self::expectExceptionObject(
            ListTypeException::forTypes("foo\[bar]", ['int']),
        );

        ListTypeException::assertItemTypeForKey('foo', [], 'bar', ['int']);
    }
}
