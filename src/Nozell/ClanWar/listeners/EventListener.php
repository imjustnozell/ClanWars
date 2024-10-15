<?php

declare(strict_types=1);

namespace Nozell\ClanWar\listeners;

use Nozell\ClanWar\arena\Arena;
use Nozell\ClanWar\clan\Clan;
use Nozell\ClanWar\clan\ClanManager;
use Nozell\ClanWar\events\ClanCreateEvent;
use Nozell\ClanWar\events\ClanEliminateEvent;
use Nozell\ClanWar\events\PlayerEliminateEvent;
use Nozell\ClanWar\events\PlayerWarJoinEvent;
use Nozell\ClanWar\events\SetSpectatorEvent;
use Nozell\ClanWar\factory\WarFactory;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use Nozell\ClanWar\Main;
use Nozell\ClanWar\sessions\SessionManager;
use Nozell\ClanWar\utils\ClanUtils;
use Nozell\ClanWar\utils\Mode;
use Nozell\ClanWar\utils\Perms;
use Nozell\ClanWar\utils\WarState;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\utils\TextFormat as TF;
use pocketmine\player\Player;

class EventListener implements Listener
{

    public function onPlayerDeath(PlayerDeathEvent $event): void
    {
        $main = Main::getInstance();
        $player = $event->getPlayer();
        $session = SessionManager::getInstance()->getPlayerSession($player);

        if ($session !== null && WarState::getInstance()->isWarActive()) {

            $session->setRole(Mode::Spectator);
            $session->applyGameMode();
            $ev = new PlayerEliminateEvent($player, $session->getClanName());
            $ev->call();
            $player->sendMessage(TF::YELLOW . "Has sido eliminado. Ahora estás en modo espectador.");
        }

        $clan = ClanManager::getInstance();
        if (count($clan->getAllClans()) <= 1) {
            $remainingClan = array_keys($clan->getAllClans())[0];
            WarFactory::getInstance()->broadcastMessage(TF::GREEN . "¡El clan $remainingClan ha ganado la guerra de clanes!");
            WarFactory::getInstance()->celebrateWin($remainingClan);
            WarState::getInstance()->endWar();
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

    public function onPlayerJoinWar(PlayerWarJoinEvent $ev): void
    {
        $player = $ev->getPlayer();

        $clanName = $ev->getClanName();

        $warState = WarState::getInstance();
        if (!$warState->isWarWaiting()) {
            $player->sendMessage(TF::RED . "No puedes unirte a la guerra porque ya ha comenzado o no está en estado de espera.");
            $ev->cancel();
            return;
        }

        $clan = ClanManager::getInstance();

        $session = SessionManager::getInstance();

        if ($clan->getClan($clanName)->getPlayerCount() >= ClanUtils::HeightMembers) {
            $player->sendMessage(TF::RED . "Tu facción ya tiene " . ClanUtils::HeightMembers . " miembros en la guerra, no puedes unirte.");
            $ev->cancel();
            return;
        }



        $clan->addPlayerToClan($clanName, $player);

        if (!$session->hasPlayerSession($player)) {

            $session->addPlayer($player);

            $event = new ClanCreateEvent($clanName);

            $event->call();

            $session->getPlayerSession($player)->setClanName($clanName);
        }


        $player->sendMessage("¡Bienvenido a la guerra de clanes, " . $player->getName() . "!");
        $session->getPlayerSession($player)->setClanName($clanName);
        $session = $session->getPlayerSession($player);
        $session->setRole(Mode::Participant);
        $session->applyGameMode();
        Arena::getInstance()->sendPlayerToArena($player);

        if ($session !== null && $session->isParticipant()) {
            $player->sendMessage(TF::RED . "Ya estás participando en la guerra.");
            $ev->cancel();
            return;
        }
    }

    public function onPlayerEliminate(PlayerEliminateEvent $ev): void
    {
        $player = $ev->getPlayer();
        $clanName = $ev->getClanName();
        $player->sendMessage("Has sido eliminado de la guerra de clanes.");
        $clan = ClanManager::getInstance();
        $session = SessionManager::getInstance();
        $session = $session->getPlayerSession($player);

        if ($session !== null) {

            $session->setRole(Mode::Spectator);
            $session->applyGameMode();
            $session->sendToLobby();

            SessionManager::getInstance()->removePlayer($player);

            if ($clan->clanExists($clanName)) {
                $clan->removePlayerFromClan($clanName, $player);


                if ($clan->getClan($clanName)->isEmpty()) {
                    $clan->removeClan($clanName);
                    WarFactory::getInstance()->broadcastMessage(TF::RED . "El clan $clanName ha sido eliminado.");
                }
            }
        }
    }
    public function spectate(SetSpectatorEvent $ev): void
    {
        $player = $ev->getPlayer();

        if (is_null($player)) {
            SessionManager::getInstance()->addPlayer($player);

            $session = SessionManager::getInstance()->getPlayerSession($player);
            $session->setRole(Mode::Spectator);
            $session->applyGameMode();

            $player->sendMessage(TF::GREEN . "Te has unido como espectador a la guerra de clanes.");
        }

        if (!is_null($session) && $session->isSpectator()) {
            $player->sendMessage(TF::RED . "Ya estás como espectador.");
            $ev->cancel();
            return;
        }
    }

    public function ClanJoin(ClanCreateEvent $event): void
    {
        $clanName = $event->getClanName();
        $clan = ClanManager::getInstance();
        if (!$clan->clanExists($clanName)) {
            $clan->createClan($clanName);
        }
    }

    public function ClanRemove(ClanEliminateEvent $event): void
    {
        $clanName = $event->getClanName();
        $clan = ClanManager::getInstance();

        if ($clan->clanExists($clanName)) {
            $clan->removeClan($clanName);
            WarFactory::getInstance()->broadcastMessage(TF::RED . "El clan $clanName ha sido eliminado.");
        }
    }
}
