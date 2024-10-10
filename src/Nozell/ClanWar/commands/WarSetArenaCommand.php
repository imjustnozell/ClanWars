<?php

declare(strict_types=1);

namespace Nozell\ClanWar\commands;

use CortexPE\Commando\BaseSubCommand;
use Nozell\ClanWar\Main;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;

class WarSetArenaCommand extends BaseSubCommand
{

    protected function prepare(): void
    {
        $this->setPermission("clanwar.command.setarena");
    }

    public function onRun(CommandSender $sender, string $label, array $args): void
    {
        if (!$sender->hasPermission("clanwar.command.setarena")) {
            $sender->sendMessage(TF::RED . "No tienes permiso para usar este comando.");
            return;
        }

        if (!$sender instanceof Player) {
            $sender->sendMessage(TF::RED . "Este comando solo puede ser usado por jugadores.");
            return;
        }

        $pos = $sender->getPosition();
        $arenaCoords = $pos->getX() . "," . $pos->getY() . "," . $pos->getZ();
        Main::getInstance()->getWarFactory()->setArena($arenaCoords);
        $sender->sendMessage(TF::GREEN . "¡Arena establecida en las coordenadas: $arenaCoords!");
    }
}
