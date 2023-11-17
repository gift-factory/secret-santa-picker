<?php

declare(strict_types=1);

namespace GiftFactory\SecretSanta;

use GiftFactory\SecretSanta\Exception\EmptyListException;
use GiftFactory\SecretSanta\Exception\MaximumTriesReached;
use GiftFactory\SecretSanta\Exception\NotEnoughPlayers;

final readonly class Picker
{
    public function __construct(
        private int $maxTries = 64,
    ) {
    }

    public function pick(PlayerList $players): Draw
    {
        NotEnoughPlayers::expectAtLeast(2, $players->getNumberOfPlayers());

        for ($i = 0; $i < $this->maxTries; $i++) {
            try {
                return $this->tryToPick($players);
            } catch (EmptyListException) {
                // continue
            }
        }

        throw MaximumTriesReached::after($this->maxTries);
    }

    private function tryToPick(PlayerList $players): Draw
    {
        $donors = $players;

        return new Draw(array_map(
            static function (Player $player) use (&$donors): array {
                $donor = $donors->without($player->getExclusions())->pick();
                $donors = $donors->without($donor);

                return [$donor, $player];
            },
            $players->getShuffledPlayers(),
        ));
    }
}
