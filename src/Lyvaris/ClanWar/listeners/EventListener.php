<?php

declare(strict_types=1);

namespace Lyvaris\ClanWar\listeners;

use Lyvaris\ClanWar\clan\ClanManager;
use Lyvaris\ClanWar\events\PlayerEliminateEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use Lyvaris\ClanWar\Main;
use Lyvaris\ClanWar\sessions\SessionManager;
use Lyvaris\ClanWar\utils\Mode;
use Lyvaris\ClanWar\utils\Perms;
use Lyvaris\ClanWar\utils\WarState;
use Lyvaris\ClanWar\utils\WarUtils;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\utils\TextFormat as TF;
use pocketmine\player\Player;

class EventListener implements Listener
{

    public function onPlayerDeath(PlayerDeathEvent $event): void
    {
        $player = $event->getPlayer();
        $session = SessionManager::getInstance()->getPlayerSession($player);

        $source = $player->getLastDamageCause();
        if (!$source instanceof EntityDamageByEntityEvent) return;
        $killer = $source->getDamager();
        if ($killer instanceof Player) {
            $killerSession = SessionManager::getInstance()->getPlayerSession($killer);
        }

        if ($session !== null && WarState::getInstance()->isWarActive()) {
            $session->setRole(Mode::Spectator);
            $session->applyGameMode();

            $ev = new PlayerEliminateEvent($player, $session->getClanName());
            $ev->call();
            $player->sendMessage(TF::YELLOW . "Has sido eliminado. Ahora estás en modo espectador.");
        }

        if (WarState::getInstance()->isWarActive() && isset($killerSession)) {
            $clanManager = ClanManager::getInstance();

            if (count($clanManager->getAllClans()) <= 1) {
                $clanName = $killerSession->getClanName();
                WarUtils::getInstance()->broadcastMessage(TF::GREEN . "¡El clan {$clanName} ha ganado la guerra de clanes!");
                WarUtils::getInstance()->celebrateWin($clanName);
                WarState::getInstance()->endWar();
            }
        }
    }

    public function onPlayerQuit(PlayerQuitEvent $event): void
    {
        $main = Main::getInstance();
        $player = $event->getPlayer();
        $session = SessionManager::getInstance()->getPlayerSession($player);
        if (WarState::getInstance()->isWarActive()) {
            $ev = new PlayerEliminateEvent($player, $session->getClanName());
            $ev->call();
        }
    }

    public function onPlayerDamage(EntityDamageByEntityEvent $event): void
    {
        $main = Main::getInstance();
        $damager = $event->getDamager();
        $entity = $event->getEntity();

        if ($damager instanceof Player && $entity instanceof Player) {
            $damagerSession = SessionManager::getInstance()->getPlayerSession($damager);
            $entitySession = SessionManager::getInstance()->getPlayerSession($entity);

            if ($damagerSession !== null && $entitySession !== null && WarState::getInstance()->isWarWaiting()) {

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


        if ($message === "si" && $player->hasPermission(Perms::admin)) {
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
