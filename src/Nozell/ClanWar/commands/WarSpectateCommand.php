<?php

declare(strict_types=1);

namespace Nozell\ClanWar\commands;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use Nozell\ClanWar\Main;
use Nozell\ClanWar\sessions\PlayerSession;

class WarSpectateCommand extends BaseSubCommand
{
    protected function prepare(): void
    {
        $this->setPermission("clanwar.command.spectate");
    }

    public function onRun(CommandSender $sender, string $label, array $args): void
    {
        $main = Main::getInstance();
        if (!$sender instanceof Player) {
            $sender->sendMessage(TF::RED . "Este comando solo puede ser usado por jugadores.");
            return;
        }

        if (!$main->getWarFactory()->isWarActive()) {
            $sender->sendMessage(TF::RED . "¡No hay ninguna guerra activa en este momento!");
            return;
        }

        $session = $main->getWarFactory()->getPlayerSession($sender);
        if ($session !== null && $session->isSpectator()) {
            $sender->sendMessage(TF::RED . "Ya estás como espectador.");
            return;
        }

        if ($session === null) {
            $session = new PlayerSession($sender);
            $main->getWarFactory()->addPlayerSession($sender, $session);
        }

        $session->setRole("spectator");
        $session->applyGameMode();

        $sender->sendMessage(TF::GREEN . "Te has unido como espectador a la guerra de clanes.");
    }
}
