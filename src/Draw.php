<?php

declare(strict_types=1);

namespace GiftFactory\SecretSanta;

use Generator;
use GiftFactory\SecretSanta\Exception\ListTypeException;
use IteratorAggregate;

/** @implements IteratorAggregate<Player, Player> */
final readonly class Draw implements IteratorAggregate
{
    private const int DONOR_COLUMN = 0;
    private const int RECEIVER_COLUMN = 1;

    public function __construct(
        public array $result,
    ) {
        ListTypeException::assertItemType('result', $result, ['array']);

        foreach ($result as $index => $item) {
            ListTypeException::assertCountAndItemType("result[$index]", $item, 2, [Player::class]);
        }
    }

    /**
     * @param array{
     *     result: list<Player|array{
     *       userName: string,
     *       realName?: string|null,
     *       address?: string|null,
     *       phoneNumber?: string|null,
     *       email?: string|null,
     *       exclusions?: list<Player|string>,
     *       wishes?: list<string>,
     *       notes?: string|null,
     *     }>
     *   } $data
     */
    public static function fromArray(array $data): self
    {
        $data['result'] = array_map(
            static fn (mixed $duo) => is_array($duo) ? array_map(
                static fn (mixed $player) => is_array($player) ? new Player(...$player) : $player,
                $duo,
            ) : $duo,
            $data['result'],
        );

        return new self(...$data);
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
