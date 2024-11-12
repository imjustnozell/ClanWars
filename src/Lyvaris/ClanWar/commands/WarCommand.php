<?php

declare(strict_types=1);

namespace Lyvaris\ClanWar\commands;

use CortexPE\Commando\BaseCommand;
use Lyvaris\ClanWar\commands\subcommands\WarInfoCommand;
use Lyvaris\ClanWar\commands\subcommands\WarJoinCommand;
use Lyvaris\ClanWar\commands\subcommands\WarKickCommand;
use Lyvaris\ClanWar\commands\subcommands\WarLeaveCommand;
use Lyvaris\ClanWar\commands\subcommands\WarSetArenaCommand;
use Lyvaris\ClanWar\commands\subcommands\WarSpectateCommand;
use Lyvaris\ClanWar\commands\subcommands\WarStartCommand;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use Lyvaris\ClanWar\Main;
use Lyvaris\ClanWar\utils\Perms;

class WarCommand extends BaseCommand
{


    protected function prepare(): void
    {
        $this->setPermission(Perms::Default);
        $main = Main::getInstance();
        $this->registerSubCommand(new WarStartCommand($main, "start", "Inicia la guerra de clanes"));
        $this->registerSubCommand(new WarJoinCommand($main, "join", "Únete a la guerra de clanes"));
        $this->registerSubCommand(new WarSpectateCommand($main, "spectate", "Únete como espectador"));
        $this->registerSubCommand(new WarSetArenaCommand($main, "setarena", "Define la posición de la arena"));
        $this->registerSubCommand(new WarInfoCommand($main, "info", "Muestra información de la guerra"));
        $this->registerSubCommand(new WarLeaveCommand($main, "leave", "use to leave the war clans"));
        $this->registerSubCommand(new WarKickCommand($main, "kick", "kick players from tha war"));
    }

    public function onRun(CommandSender $sender, string $label, array $args): void
    {
        $sender->sendMessage(TF::RED . "Usa /war <start|join|spectate|setarena|info>");
    }
}
