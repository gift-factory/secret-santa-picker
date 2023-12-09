<?php

declare(strict_types=1);

namespace Tests\GiftFactory\SecretSanta\Exception;

use Exception;
use GiftFactory\SecretSanta\Exception\ListTypeException;
use GiftFactory\SecretSanta\Player;
use GiftFactory\SecretSanta\PlayerList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

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

    #[DataProvider('getLists')]
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

    public static function getLists(): array
    {
        $cases = [
            [true, 'wishes', ['ab', 'cd'], ['string']],
            [false, 'wishes', ['ab', 'cd', null], ['string']],
            [false, 'wishes', ['ab' => 'cd'], ['string']],
            [true, 'wishes', ['ab', 'cd', null], ['string', 'null']],
            [true, 'exclusions', ['ab', 'cd', new Player('cd')], ['string', Player::class]],
            [false, 'exclusions', ['ab', 'cd', new Player('cd'), new PlayerList()], ['string', Player::class]],
        ];

        return array_combine(array_map(
            static fn (array $parameters) => json_encode($parameters[2]) . ' ' . json_encode($parameters[3]),
            $cases,
        ), $cases);
    }
}
