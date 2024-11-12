<?php

declare(strict_types=1);

namespace Lyvaris\ClanWar\clan;

use pocketmine\player\Player;

class Clan
{
    private string $name;
    private array $players = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addPlayer(Player $player): void
    {
        $this->players[$player->getName()] = $player;
    }

    public function removePlayer(Player $player): void
    {
        unset($this->players[$player->getName()]);
    }

    public function getPlayer(string $playerName): ?Player
    {
        return $this->players[$playerName] ?? null;
    }

    public function hasPlayer(Player $player): bool
    {
        return isset($this->players[$player->getName()]);
    }

    public function getPlayers(): array
    {
        return $this->players;
    }

    public function isEmpty(): bool
    {
        return empty($this->players);
    }

    public function getPlayerCount(): int
    {
        return count($this->players);
    }

    public function removeAllPlayers(): void
    {
        $this->players = [];
    }
}
