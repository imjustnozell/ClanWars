<?php

declare(strict_types=1);

namespace Nozell\ClanWar\tasks;

use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as TF;
use Nozell\ClanWar\Main;

class WarStartTask extends Task
{

    private int $countdown;

    public function __construct(int $countdown = 60)
    {
        $this->countdown = $countdown;
    }

    public function onRun(): void
    {
        $main = Main::getInstance();
        if ($this->countdown > 0) {

            $main->getServer()->broadcastMessage(TF::YELLOW . "La guerra de clanes comenzará en " . $this->countdown . " segundos.");
            $this->countdown--;
        } else {

            $main->getWarFactory()->startWar();
            $main->getScheduler()->scheduleTask($this)->cancel();
        }
    }
}
