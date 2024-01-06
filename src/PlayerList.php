<?php

declare(strict_types=1);

namespace GiftFactory\SecretSanta;

use Generator;
use GiftFactory\SecretSanta\Exception\DuplicateUserName;
use GiftFactory\SecretSanta\Exception\EmptyListException;
use GiftFactory\SecretSanta\Exception\InvalidPlayer;
use GiftFactory\SecretSanta\Exception\ListTypeException;
use GiftFactory\SecretSanta\Exception\PlayerNotFound;
use GiftFactory\SecretSanta\Exception\UserNameNotFound;
use IteratorAggregate;

use function count;
use function in_array;

/** @implements IteratorAggregate<Player> */
final readonly class PlayerList implements IteratorAggregate
{
    /** @var array<string, int> */
    private array $usernames;

    /** @var list<Player> */
    public array $players;

    public function __construct(
        /** @var list<Player|Player[]> $players */
        array $players = [],
    ) {
        /** @var array<string, int> $usernames */
        $usernames = [];
        /** @var list<Player> $list */
        $list = [];

        foreach ($players as $index => $playerOrGroup) {
            /** @var list<Player> $group */
            $group = is_array($playerOrGroup) ? $playerOrGroup : [$playerOrGroup];

            try {
                ListTypeException::assertItemType('group', $group, [Player::class]);
            } catch (ListTypeException $exception) {
                throw InvalidPlayer::atIndex($index, previous: $exception);
            }

            foreach ($group as $player) {
                $previousIndex = $usernames[$player->userName] ?? null;

                if ($previousIndex !== null) {
                    throw DuplicateUserName::atIndexes($previousIndex, $index, $player->userName);
                }

                $usernames[$player->userName] = $index;
                $list[] = $player->withExclusions($group);
            }
        }

        $this->players = $list;
        $this->usernames = $usernames;
    }

    public static function fromString(string $playerList): self
    {
        return new self(array_merge(
            ...array_map(
                static function (string $entry): array {
                    /** @psalm-suppress PossiblyUndefinedArrayOffset */
                    [$people, $address] = array_map(
                        trim(...),
                        // Adding newline to make $address fallback to empty string if string has only 1 line
                        explode("\n", "$entry\n", 2),
                    );

                    $players = array_map(
                        static function (string $userName) use ($address): Player {
                            $realName = null;

                            if (preg_match('/^(.+)\((.+)\)$/', trim($userName), $match)) {
                                $userName = trim($match[2]);
                                $realName = trim($match[1]);
                            }

                            return new Player(
                                trim($userName),
                                $realName,
                                $address,
                            );
                        },
                        preg_split('/[;,&]/', $people),
                    );

                    if (count($players) < 2) {
                        return $players;
                    }

                    return array_map(
                        static fn (Player $player) => $player->withExclusions($players),
                        $players,
                    );
                },
                preg_split("/\n[_—-]{2,}\n/", $playerList),
            ),
        ));
    }

    public static function fromArray(array $data): self
    {
        $data['players'] = array_map(
            static fn (mixed $player) => is_array($player) ? new Player(...$player) : $player,
            $data['players'],
        );

        return new self(...$data);
    }

    public function getByUserName(string $userName): Player
    {
        return $this->players[
            $this->usernames[$userName] ?? throw UserNameNotFound::for($userName)
        ] ?? throw PlayerNotFound::forUserName($userName);
    }

    public function getNumberOfPlayers(): int
    {
        return count($this->players);
    }

    /** @return list<Player> */
    public function getShuffledPlayers(): array
    {
        $players = $this->players;
        shuffle($players);

        return $players;
    }

    /** @return Generator<Player> */
    public function getIterator(): Generator
    {
        yield from $this->getShuffledPlayers();
    }

    public function without(Player|iterable|string $players): self
    {
        $names = array_map(
            static fn (Player|string $player) => is_string($player) ? $player : $player->userName,
            match (true) {
                is_array($players)    => $players,
                is_iterable($players) => iterator_to_array($players),
                default               => [$players],
            },
        );

        return new self(array_values(array_filter(
            $this->players,
            static fn (Player $player) => !in_array($player->userName, $names, true),
        )));
    }

    public function isEmpty(): bool
    {
        return $this->players === [];
    }

    public function pick(): Player
    {
        if ($this->isEmpty()) {
            throw new EmptyListException();
        }

        return $this->players[array_rand($this->players)];
    }
}
