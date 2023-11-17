<?php

declare(strict_types=1);

namespace GiftFactory\SecretSanta;

final readonly class Player
{
    public function __construct(
        public string $userName,
        public ?string $realName = null,
        public ?string $address = null,
        public ?string $phoneNumber = null,
        public ?string $email = null,
        /** @var list<Player|string> $exclusions */
        public array $exclusions = [],
        /** @var list<string> $wishes */
        public array $wishes = [],
        public ?string $notes = null,
    ) {
    }

    /** @return non-empty-list<Player|string> */
    public function getExclusions(): array
    {
        return [$this, ...$this->dedupeByUserName($this->exclusions)];
    }

    public function withExclusions(array $exclusions): self
    {
        $newExclusions = $this->dedupeByUserName([...$this->exclusions, ...$exclusions]);

        return $newExclusions === $this->exclusions ? $this : new self(
            $this->userName,
            $this->realName,
            $this->address,
            $this->phoneNumber,
            $this->email,
            $newExclusions,
            $this->wishes,
            $this->notes,
        );
    }

    private function dedupeByUserName(array $players): array
    {
        $byUserName = [];

        foreach ($players as $player) {
            $userName = is_string($player) ? $player : $player->userName;

            if ($userName !== $this->userName) {
                $byUserName[$userName] ??= $player;
            }
        }

        return array_values($byUserName);
    }
}
