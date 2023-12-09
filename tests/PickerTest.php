<?php

declare(strict_types=1);

namespace Tests\GiftFactory\SecretSanta;

use GiftFactory\SecretSanta\Draw;
use GiftFactory\SecretSanta\Exception\EmptyListException;
use GiftFactory\SecretSanta\Exception\ListTypeException;
use GiftFactory\SecretSanta\Exception\MaximumTriesReached;
use GiftFactory\SecretSanta\Exception\NotEnoughPlayers;
use GiftFactory\SecretSanta\Picker;
use GiftFactory\SecretSanta\Player;
use GiftFactory\SecretSanta\PlayerList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Picker::class)]
#[CoversClass(Player::class)]
#[CoversClass(PlayerList::class)]
#[CoversClass(Draw::class)]
#[CoversClass(NotEnoughPlayers::class)]
#[CoversClass(MaximumTriesReached::class)]
#[CoversClass(EmptyListException::class)]
#[UsesClass(ListTypeException::class)]
final class PickerTest extends TestCase
{
    public function testPick(): void
    {
        $list = new PlayerList([
            new Player('Anna'),
            new Player('Bob'),
            new Player('Chuck', exclusions: ['Bob', 'Dane']),
            new Player('Dane'),
            new Player('Fiona', exclusions: ['Anna']),
        ]);
        $names = ['Anna', 'Bob', 'Chuck', 'Dane', 'Fiona'];

        $picker = new Picker();
        $draw = $picker->pick($list);

        self::assertInstanceOf(Draw::class, $draw);

        $array = [];

        foreach ($draw as $donor => $receiver) {
            self::assertInstanceOf(Player::class, $donor);
            self::assertInstanceOf(Player::class, $receiver);

            $array[$donor->userName] = $receiver->userName;
        }

        self::assertSame($draw->getNames(), $array);

        $donors = array_keys($array);
        $receivers = array_values($array);
        sort($donors);
        sort($receivers);

        self::assertSame(
            $names,
            $donors,
            'Each player gives 1 gift',
        );
        self::assertSame(
            $names,
            $receivers,
            'Each player receives 1 gift',
        );
        self::assertNotSame(
            'Chuck',
            $array['Bob'],
            'Bob is excluded by Chuck so it should not be their donor',
        );
        self::assertNotSame(
            'Chuck',
            $array['Dane'],
            'Dane is excluded by Chuck so it should not be their donor',
        );
        self::assertNotSame(
            'Fiona',
            $array['Anna'],
            'Anna is excluded by Fiona so it should not be their donor',
        );

        foreach ($names as $name) {
            self::assertNotSame(
                $name,
                $array[$name],
                "$name should not be their own donor",
            );
        }
    }

    public function testPickGroups(): void
    {
        $list = new PlayerList([
            new Player('Anna'),
            [new Player('Bob'), new Player('Chuck'), new Player('Dane')],
            new Player('Edith'),
            [new Player('Fiona'), new Player('Gary')],
        ]);
        $names = ['Anna', 'Bob', 'Chuck', 'Dane', 'Edith', 'Fiona', 'Gary'];

        $picker = new Picker();
        $draw = $picker->pick($list);

        self::assertInstanceOf(Draw::class, $draw);

        $array = [];

        foreach ($draw as $donor => $receiver) {
            self::assertInstanceOf(Player::class, $donor);
            self::assertInstanceOf(Player::class, $receiver);

            $array[$donor->userName] = $receiver->userName;
        }

        self::assertSame($draw->getNames(), $array);

        $donors = array_keys($array);
        $receivers = array_values($array);
        sort($donors);
        sort($receivers);

        self::assertSame(
            $names,
            $donors,
            'Each player gives 1 gift',
        );
        self::assertSame(
            $names,
            $receivers,
            'Each player receives 1 gift',
        );

        foreach (['Bob', 'Chuck', 'Dane'] as $donor) {
            foreach (['Bob', 'Chuck', 'Dane'] as $receiver) {
                self::assertNotSame(
                    $receiver,
                    $array[$donor],
                    "$donor and $receiver should be mutually excluded",
                );
            }
        }

        foreach ($names as $name) {
            self::assertNotSame(
                $name,
                $array[$name],
                "$name should not be their own donor",
            );
        }
    }

    public function testMinimumNumberOfPlayer(): void
    {
        self::expectExceptionObject(NotEnoughPlayers::forMinimum(2));

        $list = new PlayerList([
            new Player('Alone'),
        ]);

        $picker = new Picker();
        $picker->pick($list);
    }

    public function testMaximumTriesReached(): void
    {
        self::expectExceptionObject(MaximumTriesReached::after(64));

        $list = new PlayerList([
            new Player('Bip bip', exclusions: ['Coyote']),
            new Player('Coyote', exclusions: ['Bip bip']),
        ]);

        $picker = new Picker();
        $picker->pick($list);
    }
}
