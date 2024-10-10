<?php

declare(strict_types=1);

namespace Nozell\ClanWar\listeners;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use Nozell\ClanWar\Main;
use Nozell\ClanWar\utils\Mode;
use pocketmine\event\player\PlayerChatEvent;
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

            $session->setRole(Mode::Spectator);
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
        if (!$player instanceof Player) return;

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

    public function onPlayerChat(PlayerChatEvent $event): void
    {
        $main = Main::getInstance();
        $message = strtolower(trim($event->getMessage()));
        $player = $event->getPlayer();


        if ($message === "si" && $player->hasPermission("clanwar.command.kick")) {
            $kickQueueManager = $main->getKickQueueManager();


            foreach ($kickQueueManager->getActiveVotes() as $targetName => $voters) {
                if (!$kickQueueManager->hasVoted($targetName, $player->getName())) {

                    $kickQueueManager->addVote($targetName, $player->getName());

                    $remainingVotes = $kickQueueManager->getRemainingVotes($targetName);
                    if ($remainingVotes <= 0) {
                        $main->getServer()->broadcastMessage(TF::RED . "El jugador " . $targetName . " ha sido expulsado por votación.");
                    } else {
                        $main->getServer()->broadcastMessage(TF::YELLOW . $player->getName() . " ha votado para expulsar a " . $targetName . ". Faltan $remainingVotes votos.");
                    }
                } else {
                    $player->sendMessage(TF::RED . "Ya has votado en esta votación.");
                }
            }
        }
    }
}
