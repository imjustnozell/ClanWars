<?php

declare(strict_types=1);

namespace Nozell\ClanWar\tasks;

use Nozell\ClanWar\clan\ClanManager;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as TF;
use Nozell\ClanWar\Main;
use Nozell\ClanWar\sessions\SessionManager;
use Nozell\ClanWar\utils\ClanUtils;
use Nozell\ClanWar\utils\WarState;
use Nozell\ClanWar\utils\WarUtils;
use pocketmine\Server;
use pocketmine\world\sound\XpCollectSound;

class WarStartTask extends Task
{
    private int $countdown;

    public function __construct(int $countdown)
    {
        $this->countdown = $countdown;

        WarState::getInstance()->setWaiting();

        Server::getInstance()->broadcastMessage(TF::YELLOW . "La guerra está en espera. Tiempo restante: " . $this->countdown . " segundos.");
        Server::getInstance()->broadcastMessage(TF::GOLD . "Asegúrate de que tu clan tenga al menos " . ClanUtils::HeightMembers . " miembros para participar.");
    }

    public function onRun(): void
    {
        $main = Main::getInstance();
        $clanManager = ClanManager::getInstance();
        $sessionManager = SessionManager::getInstance();

        if ($this->countdown > 0) {


            if ($this->countdown === 50) {
                Server::getInstance()->broadcastMessage(TF::YELLOW . "Quedan 50 segundos para que comience la guerra. Verifica tu equipo.");
            }

            if ($this->countdown === 30) {
                Server::getInstance()->broadcastMessage(TF::YELLOW . "Quedan 30 segundos para que comience la guerra. ¡Prepárate!");
            }


            if ($this->countdown <= 10) {
                foreach (Server::getInstance()->getOnlinePlayers() as $player) {


                    $session = $sessionManager->getPlayerSession($player);
                    if ($session !== null && $session->isParticipant()) {


                        $player->sendTitle(
                            TF::GOLD . (string)$this->countdown,
                            TF::GREEN . "La guerra está por comenzar...",
                            5,
                            15,
                            5
                        );


                        $world = $player->getWorld();
                        $position = $player->getPosition();
                        $sound = new XpCollectSound();
                        $world->addSound($position, $sound);
                    }
                }
            }


            $this->countdown--;
        } else {

            $clans = $clanManager->getAllClans();

            foreach ($clans as $clanName => $clan) {
                foreach ($clan->getPlayers() as $playerName => $player) {
                    $onlinePlayer = Server::getInstance()->getPlayerExact($playerName);


                    if (is_null($onlinePlayer)) {
                        $clanManager->removePlayerFromClan($clanName, $player);
                        WarUtils::getInstance()->broadcastMessage(TF::RED . "El jugador $playerName ha sido eliminado del clan $clanName por no estar online.");
                    }
                }


                if ($clan->isEmpty()) {
                    $clanManager->removeClan($clanName);
                    Server::getInstance()->broadcastMessage(TF::RED . "El clan $clanName ha sido eliminado por no tener suficientes miembros.");
                }
            }


            if (count($clanManager->getAllClans()) > 0) {
                WarState::getInstance()->setActive();
                Server::getInstance()->broadcastMessage(TF::GREEN . "¡La guerra de clanes ha comenzado!");
            } else {
                Server::getInstance()->broadcastMessage(TF::RED . "No hay suficientes clanes para iniciar la guerra.");
            }


            $this->getHandler()?->cancel();
        }
    }
}
