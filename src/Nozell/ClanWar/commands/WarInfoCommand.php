<?php

declare(strict_types=1);

namespace Nozell\ClanWar\commands;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use Nozell\ClanWar\Main;

class WarInfoCommand extends BaseSubCommand
{

    protected function prepare(): void
    {
        $this->setPermission("clanwar.command.info");
    }

    public function onRun(CommandSender $sender, string $label, array $args): void
    {
        $main = Main::getInstance();
        if (!$main->getWarFactory()->isWarActive()) {
            $sender->sendMessage(TF::RED . "No hay ninguna guerra activa en este momento.");
            return;
        }

        $timeElapsed = $main->getWarFactory()->getTimeElapsed();
        $clansAlive = $main->getWarFactory()->getClansAliveCount();

        $sender->sendMessage(TF::GREEN . "Tiempo transcurrido: " . gmdate("i:s", $timeElapsed));
        $sender->sendMessage(TF::GREEN . "Clanes vivos: " . $clansAlive);
    }
}