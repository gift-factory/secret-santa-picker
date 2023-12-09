<?php

declare(strict_types=1);

namespace Tests\GiftFactory\SecretSanta;

use GiftFactory\SecretSanta\Exception\DuplicateUserName;
use GiftFactory\SecretSanta\Exception\InvalidPlayer;
use GiftFactory\SecretSanta\Exception\NotEnoughPlayers;
use GiftFactory\SecretSanta\Exception\PlayerNotFound;
use GiftFactory\SecretSanta\Exception\UserNameNotFound;
use GiftFactory\SecretSanta\Player;
use GiftFactory\SecretSanta\PlayerList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PlayerList::class)]
#[CoversClass(Player::class)]
#[CoversClass(NotEnoughPlayers::class)]
#[CoversClass(InvalidPlayer::class)]
#[CoversClass(DuplicateUserName::class)]
#[CoversClass(PlayerNotFound::class)]
#[CoversClass(UserNameNotFound::class)]
final class PlayerListTest extends TestCase
{
    public function testFromString(): void
    {
        $list = PlayerList::fromString(
            <<<'EOS'
            Lois Lane (Red Tornado), Clark Kent (Superman)
            Daily Planet
            Metropolis
            ---
            Alfred Pennyworth (Thaddeus Crane), Bruce Wayne (Batman)
            Wayne Manor
            Gotham City
            ---
            Diana Prince (Wonder Woman)
            Themyscira
            EOS,
        );

        $lois = $list->getByUserName('Red Tornado');

        self::assertSame('Lois Lane', $lois->realName);
        self::assertSame("Daily Planet\nMetropolis", $lois->address);
        self::assertSame(['Superman'], array_map(static fn (Player $player) => $player->userName, $lois->exclusions));

        $clark = $list->getByUserName('Superman');

        self::assertSame('Clark Kent', $clark->realName);
        self::assertSame("Daily Planet\nMetropolis", $clark->address);
        self::assertSame(['Red Tornado'], array_map(static fn (Player $player) => $player->userName, $clark->exclusions));

        $alfred = $list->getByUserName('Thaddeus Crane');

        self::assertSame('Alfred Pennyworth', $alfred->realName);
        self::assertSame("Wayne Manor\nGotham City", $alfred->address);
        self::assertSame(['Batman'], array_map(static fn (Player $player) => $player->userName, $alfred->exclusions));

        $bruce = $list->getByUserName('Batman');

        self::assertSame('Bruce Wayne', $bruce->realName);
        self::assertSame("Wayne Manor\nGotham City", $bruce->address);
        self::assertSame(['Thaddeus Crane'], array_map(static fn (Player $player) => $player->userName, $bruce->exclusions));

        $bruce = $list->getByUserName('Wonder Woman');

        self::assertSame('Diana Prince', $bruce->realName);
        self::assertSame('Themyscira', $bruce->address);
        self::assertSame([], $bruce->exclusions);

        $orders = [];

        for ($i = 0; $i < 50; $i++) {
            $names = [];

            foreach ($list as $player) {
                $names[] = $player->userName;
            }

            $orders[implode(',', $names)] = true;
            sort($names);

            self::assertSame([
                'Batman',
                'Red Tornado',
                'Superman',
                'Thaddeus Crane',
                'Wonder Woman',
            ], $names);
        }

        self::assertGreaterThan(5, count($orders));
    }

    public function testInvalidPlayer(): void
    {
        self::expectExceptionObject(InvalidPlayer::atIndex(0));

        new PlayerList([['wrong-type']]);
    }

    public function testDuplicateUserName(): void
    {
        self::expectExceptionObject(DuplicateUserName::atIndexes(0, 2, 'Dada'));

        PlayerList::fromString(
            <<<'EOS'
            Dada
            ---
            Dudu
            ---
            Dada
            EOS,
        );
    }

    public function testUserNameNotFound(): void
    {
        self::expectExceptionObject(UserNameNotFound::for('Didi'));

        PlayerList::fromString(
            <<<'EOS'
            Dada
            ---
            Dudu
            EOS,
        )->getByUserName('Didi');
    }

    public function testPlayerNotFound(): void
    {
        self::assertSame(
            "Corrupted list of players found searching for 'Didi'",
            PlayerNotFound::forUserName('Didi')->getMessage(),
        );
    }

    public function testJsonExport(): void
    {
        $expected = [
            'players' => [
                [
                    'userName' => 'Red Tornado',
                    'realName' => 'Lois Lane',
                    'address' => "Daily Planet\nMetropolis",
                    'exclusions' => [
                        'Superman',
                    ],
                ],
                [
                    'userName' => 'Superman',
                    'realName' => 'Clark Kent',
                    'address' => "Daily Planet\nMetropolis",
                    'exclusions' => [
                        'Red Tornado',
                    ],
                ],
                [

                    'userName' => 'Thaddeus Crane',
                    'realName' => 'Alfred Pennyworth',
                    'address' => "Wayne Manor\nGotham City",
                    'exclusions' => [
                        'Batman',
                    ],
                ],
                [
                    'userName' => 'Batman',
                    'realName' => 'Bruce Wayne',
                    'address' => "Wayne Manor\nGotham City",
                    'exclusions' => [
                        'Thaddeus Crane',
                    ],
                ],
                [
                    'userName' => 'Wonder Woman',
                    'realName' => 'Diana Prince',
                    'address' => 'Themyscira',
                ],
            ],
        ];
        $list = PlayerList::fromString(
            <<<'EOS'
            Lois Lane (Red Tornado), Clark Kent (Superman)
            Daily Planet
            Metropolis
            ---
            Alfred Pennyworth (Thaddeus Crane), Bruce Wayne (Batman)
            Wayne Manor
            Gotham City
            ---
            Diana Prince (Wonder Woman)
            Themyscira
            EOS,
        );
        $data = json_decode(json_encode($list), true);

        self::assertSame($expected, $data, 'should export as JSON');

        $data = json_decode(json_encode(PlayerList::fromArray($data)), true);

        self::assertSame($expected, $data, 'should be rebuilt from JSON export');
    }
}
