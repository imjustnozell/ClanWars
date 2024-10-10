<?php

declare(strict_types=1);

namespace Nozell\ClanWar\commands;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use Nozell\ClanWar\Main;
use Nozell\ClanWar\tasks\WarStartCountdownTask;
use Nozell\ClanWar\tasks\WarTask;

class WarStartCommand extends BaseSubCommand
{

    protected function prepare(): void
    {
        $this->setPermission("clanwar.command.start");
    }

    public function onRun(CommandSender $sender, string $label, array $args): void
    {
        $main = Main::getInstance();
        if (!$sender->hasPermission("clanwar.command.start")) {
            $sender->sendMessage(TF::RED . "No tienes permiso para usar este comando.");
            return;
        }

        if ($main->getWarFactory()->isWarActive()) {
            $sender->sendMessage(TF::RED . "¡La guerra ya está en marcha!");
            return;
        }

        $main->getScheduler()->scheduleRepeatingTask(new WarStartCountdownTask(180), 20);
        $sender->sendMessage(TF::YELLOW . "El contador para la guerra ha comenzado. Los clanes tienen 60 segundos para completar al menos 6 miembros.");
    }
}
