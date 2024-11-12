<?php

declare(strict_types=1);

namespace Lyvaris\ClanWar\commands;

use CortexPE\Commando\BaseSubCommand;
use Lyvaris\ClanWar\arena\Arena;
use Lyvaris\ClanWar\Main;
use Lyvaris\ClanWar\utils\Perms;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;

class WarSetArenaCommand extends BaseSubCommand
{

    protected function prepare(): void
    {
        $this->setPermission(Perms::admin);
    }

    public function onRun(CommandSender $sender, string $label, array $args): void
    {


        if (!$sender instanceof Player) {
            $sender->sendMessage(TF::RED . "Este comando solo puede ser usado por jugadores.");
            return;
        }

        $pos = $sender->getPosition();
        $arenaCoords = $pos->getX() . "," . $pos->getY() . "," . $pos->getZ();
        Arena::getInstance()->setArena($arenaCoords);
        $sender->sendMessage(TF::GREEN . "¡Arena establecida en las coordenadas: $arenaCoords!");
    }
}
