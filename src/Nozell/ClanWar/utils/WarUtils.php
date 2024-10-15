<?php

namespace Nozell\ClanWar\utils;

use Nozell\ClanWar\clan\ClanManager;
use Nozell\ClanWar\Main;
use Nozell\ClanWar\sessions\SessionManager;
use Nozell\ClanWar\tasks\WarCelebrate;
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


class WarUtils
{
    use SingletonTrait;

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

        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new WarCelebrate($world, $position), 20);
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
