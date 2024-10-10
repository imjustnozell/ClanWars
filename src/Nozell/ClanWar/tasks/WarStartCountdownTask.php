<?php

declare(strict_types=1);

namespace Nozell\ClanWar\tasks;

use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as TF;
use Nozell\ClanWar\Main;
use Nozell\ClanWar\utils\ClanUtils;
use pocketmine\Server;

class WarStartCountdownTask extends Task
{
    private int $countdown;

    public function __construct(int $countdown)
    {
        $this->countdown = $countdown;
        $main = Main::getInstance();

        $main->getWarFactory()->startWarCountdown();
    }

    public function onRun(): void
    {
        $main = Main::getInstance();

        if ($this->countdown > 0) {

            Server::getInstance()->broadcastMessage(TF::YELLOW . "La guerra está en espera. Tiempo restante: " . $this->countdown . " segundos.");
            Server::getInstance()->broadcastMessage(TF::GOLD . "Asegúrate de que tu clan tenga al menos " . ClanUtils::HeightMembers . " miembros para participar.");
            $this->countdown--;
        } else {

            $clans = $main->getWarFactory()->getClans();

            foreach ($clans as $clanName => $members) {
                if (count($members) < ClanUtils::HeightMembers) {

                    $main->getWarFactory()->removeClan($clanName);
                    Server::getInstance()->broadcastMessage(TF::RED . "El clan $clanName ha sido eliminado por no cumplir con el requisito de " . ClanUtils::HeightMembers . " miembros.");
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
