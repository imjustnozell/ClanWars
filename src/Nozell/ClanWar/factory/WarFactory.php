<?php

namespace Nozell\ClanWar\factory;

use Nozell\ClanWar\clan\ClanManager;
use Nozell\ClanWar\Main;
use Nozell\ClanWar\sessions\SessionManager;
use Nozell\ClanWar\utils\ClanUtils;
use Nozell\ClanWar\utils\WarState;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use pocketmine\world\particle\HugeExplodeParticle;
use pocketmine\world\particle\HappyVillagerParticle;
use pocketmine\world\sound\PopSound;
use pocketmine\world\sound\AnvilFallSound;
use pocketmine\Server;
use pocketmine\scheduler\Task;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\SingletonTrait;


class WarFactory
{
    use SingletonTrait;

    public function startWar(): void
    {
        WarState::getInstance()->setActive();

        $clan = ClanManager::getInstance()->getInstance();

        foreach ($clan->getAllClans() as $clanName => $members) {
            if (count($members) < ClanUtils::HeightMembers) {
                $clan->removeClan($clanName);
                $this->broadcastMessage(TF::RED . "El clan $clanName ha sido eliminado por no tener suficientes miembros.");
            }
        }

        if (count($clan->getAllClans()) > 1) {
            $this->broadcastMessage(TF::YELLOW . "La guerra de clanes ha comenzado.");
        } else {
            $this->broadcastMessage(TF::RED . "No hay suficientes clanes para iniciar la guerra.");
            WarState::getInstance()->endWar();
        }
    }


    public function celebrateWin(string $clanName): void
    {
        $clan = ClanManager::getInstance()->getClan($clanName);

        if ($clan !== null) {
            foreach ($clan->getPlayers() as $player) {
                if ($player instanceof Player && SessionManager::getInstance()->getPlayerSession($player)?->isAlive()) {
                    $this->startCelebrationEffects($player);

                    $player->sendMessage(TF::GOLD . "¡Felicitaciones! Tu clan ha ganado la guerra de clanes.");
                }
            }

            Server::getInstance()->broadcastMessage(TF::GREEN . "¡El clan $clanName ha ganado la guerra de clanes!");


            Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($clanName) {
                $this->removePlayersAndClan($clanName);
            }), 120);
        }
    }

    private function startCelebrationEffects(Player $player): void
    {
        $world = $player->getWorld();
        $position = $player->getPosition();

        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new class($world, $position) extends Task {
            private $count = 0;
            private $world;
            private $position;

            public function __construct($world, $position)
            {
                $this->world = $world;
                $this->position = $position;
            }

            public function onRun(): void
            {

                $this->world->addParticle($this->position, new HugeExplodeParticle());
                $this->world->addParticle($this->position, new HappyVillagerParticle());
                $this->world->addSound($this->position, new PopSound());
                $this->world->addSound($this->position, new AnvilFallSound());


                $this->count++;
                if ($this->count >= 5) {
                    $this->getHandler()?->cancel();
                }
            }
        }, 20);
    }

    private function removePlayersAndClan(string $clanName): void
    {
        $clan = ClanManager::getInstance()->getClan($clanName);

        if ($clan !== null) {

            foreach ($clan->getPlayers() as $player) {
                if ($player instanceof Player) {
                    $player->sendMessage(TF::RED . "Has sido removido del clan $clanName después de la victoria.");
                    SessionManager::getInstance()->removePlayer($player);
                }
            }

            ClanManager::getInstance()->removeClan($clanName);
            Server::getInstance()->broadcastMessage(TF::RED . "El clan $clanName ha sido eliminado.");
        }
    }

    public function broadcastMessage(string $message): void
    {
        foreach (SessionManager::getInstance()->getAllSessions() as $session) {
            $session->getPlayer()->sendMessage($message);
        }
    }
}
