<?php

declare(strict_types=1);

namespace Nozell\ClanWar\factory;

use pocketmine\player\Player;
use Nozell\ClanWar\sessions\PlayerSession;
use pocketmine\utils\TextFormat as TF;
use pocketmine\world\particle\HappyVillagerParticle;
use pocketmine\world\sound\PopSound;

class WarFactory
{
    private bool $isActive = false;
    private int $startTime;
    private array $clans = [];
    private array $sessions = [];
    private ?string $arena = null;


    public function startWar(): void
    {
        $this->isActive = true;
        $this->startTime = time();


        foreach ($this->clans as $clanName => $members) {
            if (count($members) < 6) {
                unset($this->clans[$clanName]);
                $this->broadcastMessage(TF::RED . "El clan $clanName ha sido eliminado por no tener suficientes miembros.");
            }
        }


        if (count($this->clans) > 0) {
            $this->broadcastMessage(TF::YELLOW . "La guerra de clanes ha comenzado.");
        } else {
            $this->broadcastMessage(TF::RED . "No hay suficientes clanes para iniciar la guerra.");
            $this->isActive = false;
        }
    }


    public function isWarActive(): bool
    {
        return $this->isActive;
    }


    public function getTimeElapsed(): int
    {
        return time() - $this->startTime;
    }


    public function addClan(string $clanName, Player $player): void
    {

        if (!isset($this->clans[$clanName])) {
            $this->clans[$clanName] = [];
        }


        $this->clans[$clanName][$player->getName()] = $player;


        if (!isset($this->sessions[$player->getName()])) {
            $session = new PlayerSession($player);
            $session->setClanName($clanName);
            $this->addPlayerSession($player, $session);
        }
    }


    public function addPlayerSession(Player $player, PlayerSession $session): void
    {
        $this->sessions[$player->getName()] = $session;
        $session->setRole("participant");
        $session->applyGameMode();
    }


    public function getPlayerSession(Player $player): ?PlayerSession
    {
        return $this->sessions[$player->getName()] ?? null;
    }


    public function setArena(string $arena): void
    {
        $this->arena = $arena;
    }


    public function getArena(): ?string
    {
        return $this->arena;
    }


    public function sendPlayerToArena(Player $player): void
    {
        if ($this->arena !== null) {
            $coords = explode(",", $this->arena);
            $player->teleport(new \pocketmine\world\Position((float) $coords[0], (float) $coords[1], (float) $coords[2], $player->getWorld()));
            $player->sendMessage(TF::GREEN . "Has sido enviado a la arena.");
        } else {
            $player->sendMessage(TF::RED . "¡La arena no está configurada!");
        }
    }

    public function removePlayer(Player $player): void
    {
        $playerName = $player->getName();
        if (isset($this->sessions[$playerName])) {
            $session = $this->sessions[$playerName];
            $session->setRole("spectator");
            $session->applyGameMode();
            $session->sendToLobby();
            unset($this->sessions[$playerName]);


            $clanName = $session->getClanName();
            if (isset($this->clans[$clanName])) {

                $this->clans[$clanName] = array_filter($this->clans[$clanName], fn($member) => $member->getName() !== $playerName);
                if (empty($this->clans[$clanName])) {

                    unset($this->clans[$clanName]);
                    $this->broadcastMessage(TF::RED . "El clan $clanName ha sido eliminado.");
                }
            }


            if (count($this->clans) === 1) {
                $remainingClan = array_keys($this->clans)[0];
                $this->broadcastMessage(TF::GREEN . "¡El clan $remainingClan ha ganado la guerra de clanes!");


                $this->celebrateWin($remainingClan);


                $this->isActive = false;
            }
        }
    }

    public function celebrateWin(string $clanName): void
    {
        if (isset($this->clans[$clanName])) {
            foreach ($this->clans[$clanName] as $memberName => $player) {
                if ($player instanceof Player && $this->isPlayerAlive($player)) {
                    $world = $player->getWorld();
                    $position = $player->getPosition();


                    $world->addParticle($position, new HappyVillagerParticle());
                    $world->addSound($position, new PopSound());


                    $player->sendMessage(TF::GOLD . "¡Felicitaciones! Tu clan ha ganado la guerra de clanes.");
                }
            }
        }
    }

    private function getPlayerByName(string $playerName): ?Player
    {
        foreach ($this->sessions as $session) {
            $player = $session->getPlayer();
            if ($player->getName() === $playerName) {
                return $player;
            }
        }
        return null;
    }

    public function isPlayerAlive(Player $player): bool
    {
        $playerName = $player->getName();
        return isset($this->sessions[$playerName]) && $this->sessions[$playerName]->isAlive();
    }

    public function getAlivePlayersInClan(string $clanName): array
    {
        if (!isset($this->clans[$clanName])) {
            return [];
        }

        return array_filter($this->clans[$clanName], fn($memberName) => $this->isPlayerAlive($this->getPlayerByName($memberName)));
    }
    public function getClansAliveCount(): int
    {
        return count($this->clans);
    }

    public function removeClan(string $clanName): void
    {
        if (isset($this->clans[$clanName])) {
            unset($this->clans[$clanName]);
            $this->broadcastMessage(TF::RED . "El clan $clanName ha sido eliminado.");
        }
    }

    public function broadcastMessage(string $message): void
    {
        foreach ($this->sessions as $session) {
            $session->getPlayer()->sendMessage($message);
        }
    }

    public function getClans(): array
    {
        return $this->clans;
    }

}
