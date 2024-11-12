<?php

declare(strict_types=1);

namespace Lyvaris\ClanWar\listeners;

use Lyvaris\ClanWar\arena\Arena;
use Lyvaris\ClanWar\clan\ClanManager;
use Lyvaris\ClanWar\events\ClanCreateEvent;
use Lyvaris\ClanWar\events\PlayerEliminateEvent;
use Lyvaris\ClanWar\events\PlayerWarJoinEvent;
use Lyvaris\ClanWar\events\SetSpectatorEvent;
use pocketmine\event\Listener;
use Lyvaris\ClanWar\sessions\SessionManager;
use Lyvaris\ClanWar\utils\ClanUtils;
use Lyvaris\ClanWar\utils\Mode;
use Lyvaris\ClanWar\utils\WarState;
use Lyvaris\ClanWar\utils\WarUtils;
use pocketmine\utils\TextFormat as TF;

class WarListener implements Listener
{

    public function onPlayerJoinWar(PlayerWarJoinEvent $ev): void
    {
        $player = $ev->getPlayer();
        $clanName = $ev->getClanName();
        $warState = WarState::getInstance();
        $clanManager = ClanManager::getInstance();
        $sessionManager = SessionManager::getInstance();

        if (!$warState->isWarWaiting()) {
            $player->sendMessage(TF::RED . "No puedes unirte a la guerra porque ya ha comenzado o no está en estado de espera.");
            $ev->cancel();
            return;
        }

        if ($sessionManager->hasPlayerSession($player)) {
            $playerSession = $sessionManager->getPlayerSession($player);
            if ($playerSession->isParticipant()) {
                $player->sendMessage(TF::RED . "Ya estás participando en la guerra.");
                $ev->cancel();
                return;
            }
        } else {
            $sessionManager->addPlayer($player);
            $sessionManager->getPlayerSession($player)->setClanName($clanName);
        }

        $clan = $clanManager->getClan($clanName);
        if ($clan !== null && $clan->getPlayerCount() >= ClanUtils::HeightMembers) {
            $player->sendMessage(TF::RED . "Tu facción ya tiene " . ClanUtils::HeightMembers . " miembros en la guerra, no puedes unirte.");
            $ev->cancel();
            return;
        }

        if ($clan === null) {
            $clanManager->createClan($clanName);
            $event = new ClanCreateEvent($clanName);
            $event->call();
        }

        $clanManager->addPlayerToClan($clanName, $player);

        $playerSession = $sessionManager->getPlayerSession($player);
        $playerSession->setRole(Mode::Participant);
        $playerSession->applyGameMode();

        Arena::getInstance()->sendPlayerToArena($player);

        $player->sendMessage(TF::GREEN . "¡Bienvenido a la guerra de clanes, " . $player->getName() . "!");
    }

    public function onPlayerEliminate(PlayerEliminateEvent $ev): void
    {
        $player = $ev->getPlayer();
        $clanName = $ev->getClanName();
        $player->sendMessage("Has sido eliminado de la guerra de clanes.");
        $clanManager = ClanManager::getInstance();
        $sessionManager = SessionManager::getInstance();
        $session = $sessionManager->getPlayerSession($player);

        if ($session !== null) {

            $session->setRole(Mode::Spectator);
            $session->applyGameMode();
            $session->sendToLobby();

            $sessionManager->removePlayer($player);

            if ($clanManager->clanExists($clanName)) {
                $clanManager->removePlayerFromClan($clanName, $player);

                $clan = $clanManager->getClan($clanName);
                if ($clan !== null && empty($clan->getPlayers())) {
                    $clanManager->removeClan($clanName);
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
