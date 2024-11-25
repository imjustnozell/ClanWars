<?php

declare(strict_types=1);

namespace Nozell\ClanWar\commands\subcommands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use Nozell\ClanWar\Main;
use Nozell\ClanWar\sessions\SessionWarManager;
use Nozell\ClanWar\utils\Perms;
use pocketmine\Server;

class WarKickCommand extends BaseSubCommand
{
    protected function prepare(): void
    {
        $this->setPermission(Perms::admin);
        $this->registerArgument(0, new RawStringArgument("player"));
    }

    public function onRun(CommandSender $sender, string $label, array $args): void
    {
        $main = Main::getInstance();

        if (!$sender instanceof Player) {
            $sender->sendMessage(TF::RED . "Este comando solo puede ser usado por jugadores.");
            return;
        }


        $target = $args["player"];
        $target = Server::getInstance()->getPlayerByPrefix($target);

        if (!$target instanceof Player) {
            $sender->sendMessage(TF::RED . "El jugador seleccionado no está en línea o no existe.");
            return;
        }

        $session = SessionWarManager::getInstance()->getPlayerSession($target);
        if (is_null($session) || !$session->isParticipant()) {
            $sender->sendMessage(TF::RED . "El jugador no está participando en la guerra.");
            return;
        }

        $kickQueueManager = $main->getKickQueueManager();
        if ($kickQueueManager->isVotingActive($target->getName())) {
            $sender->sendMessage(TF::YELLOW . "Ya hay una votación activa para expulsar a " . $target->getName() . ".");
            return;
        }

        $kickQueueManager->addKickRequest($target->getName(), function () use ($main, $target) {
            SessionWarManager::getInstance()->removePlayer($target);
            $main->getServer()->broadcastMessage(TF::RED . "El jugador " . $target->getName() . " ha sido expulsado de la guerra.");
        });

        foreach ($main->getServer()->getOnlinePlayers() as $player) {
            if ($player->hasPermission(Perms::admin)) {
                $player->sendMessage(TF::YELLOW . "Votación para expulsar a " . $target->getName() . " iniciada por " . $sender->getName() . ".");
                $player->sendMessage(TF::YELLOW . "Escribe 'sí' en el chat para votar.");
            }
        }
    }
}
