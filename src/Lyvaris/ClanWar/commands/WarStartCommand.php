<?php

declare(strict_types=1);

namespace Lyvaris\ClanWar\commands;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use Lyvaris\ClanWar\Main;
use Lyvaris\ClanWar\tasks\WarStartCountdownTask;
use Lyvaris\ClanWar\tasks\WarStartTask;
use Lyvaris\ClanWar\utils\ClanUtils;
use Lyvaris\ClanWar\utils\Perms;
use Lyvaris\ClanWar\utils\WarState;

class WarStartCommand extends BaseSubCommand
{

    protected function prepare(): void
    {
        $this->setPermission(Perms::admin);
    }

    public function onRun(CommandSender $sender, string $label, array $args): void
    {
        $main = Main::getInstance();

        if (WarState::getInstance()->isWarActive()) return;

        if (WarState::getInstance()->isWarWaiting()) return;

        $main->getScheduler()->scheduleRepeatingTask(new WarStartTask(ClanUtils::Time_Lapse), 20);
        $sender->sendMessage(TF::YELLOW . "El contador para la guerra ha comenzado. Los clanes tienen " . ClanUtils::Time_Lapse . " segundos para completar al menos " . ClanUtils::HeightMembers . " miembros.");
    }
}
