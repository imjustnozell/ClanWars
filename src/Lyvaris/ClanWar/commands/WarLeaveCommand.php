<?php

declare(strict_types=1);

namespace Lyvaris\ClanWar\commands;

use CortexPE\Commando\BaseSubCommand;
use Lyvaris\ClanWar\clan\ClanManager;
use Lyvaris\ClanWar\events\PlayerEliminateEvent;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use Lyvaris\ClanWar\Main;
use Lyvaris\ClanWar\sessions\SessionManager;
use Lyvaris\ClanWar\utils\Perms;

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

        $session = SessionManager::getInstance()->getPlayerSession($sender);

        if (is_null($session)) return;

        if ($session->isParticipant()) {
            $factionName = $session->getClanName();
            $ev = new PlayerEliminateEvent($sender, $factionName);
            $ev->call();
        } elseif ($session->isSpectator()) {

            SessionManager::getInstance()->removePlayer($sender);
            $sender->sendMessage(TF::YELLOW . "Has salido de la guerra como espectador.");
        }

        $session->sendToLobby();
    }
}
