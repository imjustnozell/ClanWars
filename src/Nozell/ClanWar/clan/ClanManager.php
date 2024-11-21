<?php

declare(strict_types=1);

namespace Nozell\ClanWar\clan;

use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

class ClanManager
{
    use SingletonTrait;
    private array $clans = [];

    public function createClan(string $clanName): void
    {
        if (!isset($this->clans[$clanName])) {
            $this->clans[$clanName] = new Clan($clanName);
        }
    }

    public function removeClan(string $clanName): void
    {
        if (isset($this->clans[$clanName])) {
            unset($this->clans[$clanName]);
        }
    }

    public function addPlayerToClan(string $clanName, Player $player): void
    {
        if (isset($this->clans[$clanName])) {
            $this->clans[$clanName]->addPlayer($player);
        }
    }

    public function removePlayerFromClan(string $clanName, Player $player): void
    {
        if (isset($this->clans[$clanName])) {
            $this->clans[$clanName]->removePlayer($player);
            if ($this->clans[$clanName]->isEmpty()) {
                $this->removeClan($clanName);
            }
        }
    }

    public function getClan(string $clanName): ?Clan
    {
        return $this->clans[$clanName] ?? null;
    }

    public function clanExists(string $clanName): bool
    {
        return isset($this->clans[$clanName]);
    }

    public function getAllClans(): array
    {
        return $this->clans;
    }
}
