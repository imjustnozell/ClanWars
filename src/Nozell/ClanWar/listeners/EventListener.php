<?php

declare(strict_types=1);

namespace Nozell\ClanWar\listeners;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use Nozell\ClanWar\Main;
use pocketmine\utils\TextFormat as TF;
use pocketmine\player\Player;

class EventListener implements Listener
{

    public function onPlayerDeath(PlayerDeathEvent $event): void
    {
        $main = Main::getInstance();
        $player = $event->getPlayer();
        $session = $main->getWarFactory()->getPlayerSession($player);

        if ($session !== null && $main->getWarFactory()->isWarActive()) {

            $session->setRole("spectator");
            $session->applyGameMode();
            $main->getWarFactory()->removePlayer($player);
            $player->sendMessage(TF::YELLOW . "Has sido eliminado. Ahora estás en modo espectador.");
        }
    }

    public function onPlayerQuit(PlayerQuitEvent $event): void
    {
        $main = Main::getInstance();
        $player = $event->getPlayer();
        if ($main->getWarFactory()->isWarActive()) {
            $main->getWarFactory()->removePlayer($player);
        }
    }

    public function onPlayerCommandPreprocess(CommandEvent $event): void
    {
        $main = Main::getInstance();
        $player = $event->getSender();
        $session = $main->getWarFactory()->getPlayerSession($player);

        if ($session !== null && $session->isParticipant() && $main->getWarFactory()->isWarActive()) {
            $command = strtolower($event->getCommand());
            if (strpos($command, '/') === 0) {
                $event->cancel();
                $player->sendMessage(TF::RED . "No puedes usar comandos mientras participas en la guerra.");
            }
        }
    }

    public function onPlayerDamage(EntityDamageByEntityEvent $event): void
    {
        $main = Main::getInstance();
        $damager = $event->getDamager();
        $entity = $event->getEntity();
        
        if ($damager instanceof Player && $entity instanceof Player) {
            $damagerSession = $main->getWarFactory()->getPlayerSession($damager);
            $entitySession = $main->getWarFactory()->getPlayerSession($entity);
            
            if ($damagerSession !== null && $entitySession !== null && $main->getWarFactory()->isWarWaiting()) {
                
                $event->cancel();
                $damager->sendMessage(TF::RED . "No puedes atacar a otros jugadores mientras la guerra está en espera.");
            }
        }
    }
}
