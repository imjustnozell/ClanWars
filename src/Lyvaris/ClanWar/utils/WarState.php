<?php

namespace Lyvaris\ClanWar\utils;

use Lyvaris\ClanWar\sessions\SessionWarManager;
use Nozell\Scoreboard\Factory\ScoreboardFactory;
use Nozell\Scoreboard\Session\SessionManager;
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
        ScoreboardFactory::createCustomScoreboard(ScoreboardIds::Waiting, "waiting", ["linea 1", "Linea2"], 1);
    }

    public function setActive(): void
    {
        if ($this->isWaiting) {
            $this->isWaiting = false;
            $this->isActive = true;
            $this->hasEnded = false;
            $this->time = 0 + time();
            foreach (SessionWarManager::getInstance()->getAllSessions() as $session) {
                $player = $session->getPlayer();
                SessionManager::getSession($player)->setScoreboard(ScoreboardIds::Started);
            }
            Server::getInstance()->broadcastMessage(TF::YELLOW . "¡La guerra de clanes ha comenzado!");
            ScoreboardFactory::removeCustomScoreboard(ScoreboardIds::Waiting);
            ScoreboardFactory::createCustomScoreboard(ScoreboardIds::Started, "start", ["linea 1", "Linea2"], 1);
        }
    }

    public function endWar(): void
    {
        if ($this->isActive) {
            $this->isWaiting = false;
            $this->isActive = false;
            $this->hasEnded = true;
            Server::getInstance()->broadcastMessage(TF::GREEN . "La guerra ha terminado.");
            ScoreboardFactory::removeCustomScoreboard(ScoreboardIds::Started);
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
