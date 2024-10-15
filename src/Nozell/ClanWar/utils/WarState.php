<?php

namespace Nozell\ClanWar\utils;

use Nozell\ClanWar\factory\WarFactory;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat as TF;

class WarState
{
    use SingletonTrait;
    private bool $isActive = false;
    private bool $isWaiting = false;
    private bool $hasEnded = true;
    private int $time = 0;

    public function setWaiting(): void
    {
        $this->isWaiting = true;
        $this->isActive = false;
        $this->hasEnded = false;
        Server::getInstance()->broadcastMessage(TF::YELLOW . "La guerra está en espera. Asegúrate de que tu clan tenga al menos " . ClanUtils::HeightMembers . " miembros.");
    }

    public function setActive(): void
    {
        if ($this->isWaiting) {
            $this->isWaiting = false;
            $this->isActive = true;
            $this->hasEnded = false;
            $this->time = 0 + time();
            Server::getInstance()->broadcastMessage(TF::YELLOW . "¡La guerra de clanes ha comenzado!");
        }
    }

    public function endWar(): void
    {
        if ($this->isActive) {
            $this->isWaiting = false;
            $this->isActive = false;
            $this->hasEnded = true;
            Server::getInstance()->broadcastMessage(TF::GREEN . "La guerra ha terminado.");
        }
    }

    public function isWarActive(): bool
    {
        return $this->isActive;
    }

    public function isWarWaiting(): bool
    {
        return $this->isWaiting;
    }

    public function hasWarEnded(): bool
    {
        return $this->hasEnded;
    }

    public function getTimeElapsed(): int
    {
        return $this->time;
    }
}
