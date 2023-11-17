<?php

declare(strict_types=1);

namespace GiftFactory\SecretSanta;

use Generator;
use IteratorAggregate;

/** @implements IteratorAggregate<Player, Player> */
final readonly class Draw implements IteratorAggregate
{
    private const DONOR_COLUMN = 0;
    private const RECEIVER_COLUMN = 1;

    public function __construct(
        public array $result,
    ) {
    }

    /** @return Generator<Player, Player> */
    public function getIterator(): Generator
    {
        foreach ($this->result as $item) {
            yield $item[self::DONOR_COLUMN] => $item[self::RECEIVER_COLUMN];
        }
    }

    public function getNames(): array
    {
        return array_combine(
            $this->getNamesForColumn(self::DONOR_COLUMN),
            $this->getNamesForColumn(self::RECEIVER_COLUMN),
        );
    }

    private function getNamesForColumn(int $column): array
    {
        return array_map(
            static fn (Player $player) => $player->userName,
            array_column($this->result, $column),
        );
    }
}