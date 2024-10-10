<?php

declare(strict_types=1);

namespace Nozell\ClanWar\tasks;

use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as TF;
use Nozell\ClanWar\Main;
use pocketmine\Server;

class WarStartCountdownTask extends Task
{
    private int $countdown;

    public function __construct(int $countdown)
    {
        $this->countdown = $countdown;
    }

    public function onRun(): void
    {
        $main = Main::getInstance();
        if ($this->countdown > 0) {

            Server::getInstance()->broadcastMessage(TF::YELLOW . "Tiempo restante para la guerra: " . $this->countdown . " segundos.");
            $this->countdown--;
        } else {

            $clans = $main->getWarFactory()->getClans();
            foreach ($clans as $clanName => $members) {
                if (count($members) < 6) {

                    $main->getWarFactory()->removeClan($clanName);
                    Server::getInstance()->broadcastMessage(TF::RED . "El clan $clanName ha sido eliminado por no cumplir con el requisito de 6 miembros.");
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
