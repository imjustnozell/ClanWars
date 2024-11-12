<?php

declare(strict_types=1);

namespace Lyvaris\ClanWar\sessions;

use Lyvaris\ClanWar\utils\Mode;
use pocketmine\player\Player;
use pocketmine\player\GameMode;

class PlayerSession
{
    private Player $player;
    private string $role;
    private string $clanName = "";
    private bool $alive = true;
    private bool $inArena = false;

    public function __construct(Player $player)
    {
        $this->player = $player;
        $this->role = Mode::Spectator;
        $this->applyGameMode();
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
        $this->applyGameMode();
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function isParticipant(): bool
    {
        return $this->role === Mode::Participant;
    }

    public function isSpectator(): bool
    {
        return $this->role === Mode::Spectator;
    }

    public function setInArena(bool $inArena): void
    {
        $this->inArena = $inArena;
    }

    public function isInArena(): bool
    {
        return $this->inArena;
    }

    public function setAlive(bool $alive): void
    {
        $this->alive = $alive;
    }

    public function isAlive(): bool
    {
        return $this->alive;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function applyGameMode(): void
    {
        if ($this->isSpectator()) {
            $this->player->setGamemode(GameMode::SPECTATOR());
        } elseif ($this->isParticipant()) {
            $this->player->setGamemode(GameMode::SURVIVAL());
        }
    }

    public function sendToLobby(): void
    {
        $this->player->teleport($this->player->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
        $this->player->sendMessage("Has salido de la arena y fuiste llevado al lobby.");
    }

    public function setClanName(string $clanName): void
    {
        $this->clanName = $clanName;
    }

    public function getClanName(): string
    {
        return $this->clanName;
    }
}
