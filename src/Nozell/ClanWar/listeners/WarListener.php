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
use Nozell\ClanWar\sessions\SessionWarManager;
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
        $clanManager = ClanManager::getInstance();
        $SessionWarManager = SessionWarManager::getInstance();

        if (!$warState->isWarWaiting()) {
            $player->sendMessage(TF::RED . "No puedes unirte a la guerra porque ya ha comenzado o no está en estado de espera.");
            $ev->cancel();
            return;
        }

        if ($SessionWarManager->hasPlayerSession($player)) {
            $playerSession = $SessionWarManager->getPlayerSession($player);
            if ($playerSession->isParticipant()) {
                $player->sendMessage(TF::RED . "Ya estás participando en la guerra.");
                $ev->cancel();
                return;
            }
        } else {
            $SessionWarManager->addPlayer($player);
            $SessionWarManager->getPlayerSession($player)->setClanName($clanName);
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

        $playerSession = $SessionWarManager->getPlayerSession($player);
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
        $SessionWarManager = SessionWarManager::getInstance();
        $session = $SessionWarManager->getPlayerSession($player);

        if ($session !== null) {

            $session->setRole(Mode::Spectator);
            $session->applyGameMode();
            $session->sendToLobby();

            $SessionWarManager->removePlayer($player);

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

        if (!is_null($player)) {

            SessionWarManager::getInstance()->addPlayer($player);

            $session = SessionWarManager::getInstance()->getPlayerSession($player);
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
