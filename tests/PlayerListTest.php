<?php

declare(strict_types=1);

namespace Tests\GiftFactory\SecretSanta;

use GiftFactory\SecretSanta\Player;
use GiftFactory\SecretSanta\PlayerList;
use PHPUnit\Framework\TestCase;

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
}
