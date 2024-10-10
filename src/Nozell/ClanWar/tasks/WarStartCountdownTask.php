<?php

declare(strict_types=1);

namespace Nozell\ClanWar\tasks;

use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as TF;
use Nozell\ClanWar\Main;
use Nozell\ClanWar\utils\ClanUtils;
use pocketmine\Server;
use pocketmine\world\sound\XpCollectSound;

class WarStartCountdownTask extends Task
{
    private int $countdown;

    public function __construct(int $countdown)
    {
        $this->countdown = $countdown;
        $main = Main::getInstance();

        $main->getWarFactory()->startWarCountdown();

        Server::getInstance()->broadcastMessage(TF::YELLOW . "La guerra está en espera. Tiempo restante: " . $this->countdown . " segundos.");
        Server::getInstance()->broadcastMessage(TF::GOLD . "Asegúrate de que tu clan tenga al menos " . ClanUtils::HeightMembers . " miembros para participar.");
    }

    public function onRun(): void
    {
        $main = Main::getInstance();

        if ($this->countdown > 0) {

            if ($this->countdown === 50) {
                Server::getInstance()->broadcastMessage(TF::YELLOW . "Quedan 50 segundos para que comience la guerra. Verifica tu equipo.");
            }

            if ($this->countdown === 30) {
                Server::getInstance()->broadcastMessage(TF::YELLOW . "Quedan 30 segundos para que comience la guerra. ¡Prepárate!");
            }

            if ($this->countdown <= 10) {
                foreach (Server::getInstance()->getOnlinePlayers() as $player) {

                    $session = $main->getWarFactory()->getPlayerSession($player);
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

            $clans = $main->getWarFactory()->getClans();

            foreach ($clans as $clanName => $members) {
                foreach ($members as $playerName => $player) {
                    $onlinePlayer = Server::getInstance()->getPlayerExact($playerName);


                    if ($onlinePlayer === null) {
                        unset($clans[$clanName][$playerName]);
                        $main->getWarFactory()->broadcastMessage(TF::RED . "El jugador $playerName ha sido eliminado del clan $clanName por no estar online.");
                    }
                }

                if (empty($clans[$clanName])) {
                    $main->getWarFactory()->removeClan($clanName);
                    Server::getInstance()->broadcastMessage(TF::RED . "El clan $clanName ha sido eliminado por no tener suficientes miembros.");
                }
            }

            if (count($main->getWarFactory()->getClans()) > 0) {
                $main->getWarFactory()->startWar();
                Server::getInstance()->broadcastMessage(TF::GREEN . "¡La guerra de clanes ha comenzado!");
            } else {
                Server::getInstance()->broadcastMessage(TF::RED . "No hay suficientes clanes para iniciar la guerra.");
            }

            $this->getHandler()?->cancel();
        }
    }
}
