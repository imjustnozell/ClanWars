<?php

declare(strict_types=1);

namespace Nozell\ClanWar\commands;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use Nozell\ClanWar\Main;

class SetArenaCommand extends BaseSubCommand
{

    protected function prepare(): void
    {
        $this->setPermission("clanwar.command.setarena");
    }

    public function onRun(CommandSender $sender, string $label, array $args): void
    {
        $main = Main::getInstance();

        if ($sender instanceof Player) {
            $pos = $sender->getPosition();
            $arenaCoords = $pos->getX() . "," . $pos->getY() . "," . $pos->getZ();
            $main->getWarFactory()->setArena($arenaCoords);
            $sender->sendMessage(TF::GREEN . "¡Arena establecida en las coordenadas: $arenaCoords!");
        } else {
            $sender->sendMessage(TF::RED . "Este comando solo puede ser usado por jugadores.");
        }
    }
}
