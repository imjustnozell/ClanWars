<?php

declare(strict_types=1);

namespace Nozell\ClanWar\commands\subcommands;

use CortexPE\Commando\BaseSubCommand;
use Nozell\ClanWar\events\PlayerEliminateEvent;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use Nozell\ClanWar\Main;
use Nozell\ClanWar\sessions\SessionWarManager;
use Nozell\ClanWar\utils\Perms;

class WarLeaveCommand extends BaseSubCommand
{
    protected function prepare(): void
    {
        $this->setPermission(Perms::Default);
    }

    public function onRun(CommandSender $sender, string $label, array $args): void
    {
        $main = Main::getInstance();

        if (!$sender instanceof Player) return;

        $session = SessionWarManager::getInstance()->getPlayerSession($sender);

        if (is_null($session)) return;

        if ($session->isParticipant()) {
            $factionName = $session->getClanName();
            $ev = new PlayerEliminateEvent($sender, $factionName);
            $ev->call();
        } elseif ($session->isSpectator()) {

            SessionWarManager::getInstance()->removePlayer($sender);
            $sender->sendMessage(TF::YELLOW . "Has salido de la guerra como espectador.");
        }

        $session->sendToLobby();
    }
}
