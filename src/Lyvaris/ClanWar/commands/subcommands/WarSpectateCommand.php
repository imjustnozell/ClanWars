<?php

declare(strict_types=1);

namespace Lyvaris\ClanWar\commands\subcommands;

use CortexPE\Commando\BaseSubCommand;
use Lyvaris\ClanWar\events\SetSpectatorEvent;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use Lyvaris\ClanWar\Main;
use Lyvaris\ClanWar\utils\Perms;
use Lyvaris\ClanWar\utils\WarState;

class WarSpectateCommand extends BaseSubCommand
{
    protected function prepare(): void
    {
        $this->setPermission(Perms::Default);
    }

    public function onRun(CommandSender $sender, string $label, array $args): void
    {
        $main = Main::getInstance();
        if (!$sender instanceof Player) return;

        if (!WarState::getInstance()->isWarActive()) {
            $sender->sendMessage(TF::RED . "¡No hay ninguna guerra activa en este momento!");
            return;
        }

        $ev = new SetSpectatorEvent($sender);
        $ev->call();
        if ($ev->isCancelled()) return;
    }
}
