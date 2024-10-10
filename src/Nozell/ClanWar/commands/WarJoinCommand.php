<?php

declare(strict_types=1);

namespace Nozell\ClanWar\commands;

use CortexPE\Commando\BaseSubCommand;
use Nozell\ClanWar\sessions\PlayerSession;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use Nozell\ClanWar\Main;

class WarJoinCommand extends BaseSubCommand
{
    protected function prepare(): void
    {
        $this->setPermission("clanwar.command.join");
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
        if ($session !== null && $session->isParticipant()) {
            $sender->sendMessage(TF::RED . "Ya estás participando en la guerra.");
            return;
        }


        $newSession = new PlayerSession($sender);
        $main->getWarFactory()->addPlayerSession($sender, $newSession);


        $main->getWarFactory()->sendPlayerToArena($sender);

        $sender->sendMessage(TF::GREEN . "Te has unido a la guerra de clanes.");
    }
}
