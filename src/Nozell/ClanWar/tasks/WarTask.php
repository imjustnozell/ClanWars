<?php

declare(strict_types=1);

namespace Nozell\ClanWar\tasks;

use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as TF;
use Nozell\ClanWar\Main;

class WarTask extends Task
{


    public function onRun(): void
    {
        $main = Main::getInstance();

        if ($main->getWarFactory()->isWarActive()) {
            $timeElapsed = $main->getWarFactory()->getTimeElapsed();
            $main->getServer()->broadcastMessage(TF::YELLOW . "Tiempo transcurrido en la guerra: " . gmdate("i:s", $timeElapsed));


        } else {

            $main->getScheduler()->scheduleTask($this)->cancel();

        }
    }
}
