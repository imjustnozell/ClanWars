<?php

declare(strict_types=1);

namespace Nozell\ClanWar\sessions;

use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

class SessionManager
{
    use SingletonTrait;

    private array $sessions = [];

    public function addPlayer(Player $player): void
    {
        if (!isset($this->sessions[$player->getName()])) {
            $this->sessions[$player->getName()] = new PlayerSession($player);
        }
    }

    public function removePlayer(Player $player): void
    {
        unset($this->sessions[$player->getName()]);
    }

    public function getPlayerSession(Player $player): ?PlayerSession
    {
        return $this->sessions[$player->getName()] ?? null;
    }

    public function hasPlayerSession(Player $player): bool
    {
        return isset($this->sessions[$player->getName()]);
    }

    public function getAllSessions(): array
    {
        return $this->sessions;
    }

    public function getAlivePlayers(): array
    {
        return array_filter($this->sessions, fn(PlayerSession $session) => $session->isAlive());
    }

    public function clearAllSessions(): void
    {
        $this->sessions = [];
    }
}