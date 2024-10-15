<?php

declare(strict_types=1);

namespace Nozell\ClanWar\listeners;

use Nozell\ClanWar\arena\Arena;
use Nozell\ClanWar\clan\ClanManager;
use Nozell\ClanWar\events\ClanCreateEvent;
use Nozell\ClanWar\events\PlayerEliminateEvent;
use Nozell\ClanWar\events\PlayerWarJoinEvent;
use Nozell\ClanWar\events\SetSpectatorEvent;
use pocketmine\event\Listener;
use Nozell\ClanWar\sessions\SessionManager;
use Nozell\ClanWar\utils\ClanUtils;
use Nozell\ClanWar\utils\Mode;
use Nozell\ClanWar\utils\WarState;
use Nozell\ClanWar\utils\WarUtils;
use pocketmine\utils\TextFormat as TF;

class WarListener implements Listener
{

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

        if (is_null($clanName)) return;

        $clan = ClanManager::getInstance();

        $session = SessionManager::getInstance();

        $clan->addPlayerToClan($clanName, $player);

        if (!$session->hasPlayerSession($player)) {

            $session->addPlayer($player);

            $event = new ClanCreateEvent($clanName);

            $event->call();

            $session->getPlayerSession($player)->setClanName($clanName);
        }

        if ($clan->getClan($clanName)->getPlayerCount() >= ClanUtils::HeightMembers) {
            $player->sendMessage(TF::RED . "Tu facción ya tiene " . ClanUtils::HeightMembers . " miembros en la guerra, no puedes unirte.");
            $ev->cancel();
            return;
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
                    WarUtils::getInstance()->broadcastMessage(TF::RED . "El clan $clanName ha sido eliminado.");
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
}
