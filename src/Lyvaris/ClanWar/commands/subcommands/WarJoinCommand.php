<?php

declare(strict_types=1);

namespace Lyvaris\ClanWar\commands\subcommands;

use CortexPE\Commando\BaseSubCommand;
use Lyvaris\ClanWar\events\PlayerWarJoinEvent;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use Lyvaris\ClanWar\Main;
use Lyvaris\ClanWar\utils\Perms;
use rxduz\factions\player\FactionPlayer;
use rxduz\factions\player\PlayerManager;
use rxduz\factions\faction\FactionManager;

class WarJoinCommand extends BaseSubCommand
{
    protected function prepare(): void
    {
        $this->setPermission(Perms::Default);
    }

    public function onRun(CommandSender $sender, string $label, array $args): void
    {
        $main = Main::getInstance();


        if (!$sender instanceof Player) return;

        $factionPlayer = PlayerManager::getInstance()->getSessionByName($sender->getName());
        if (!$factionPlayer instanceof FactionPlayer || !$factionPlayer->inFaction()) {
            $sender->sendMessage(TF::RED . "No puedes unirte a la guerra si no perteneces a una facción.");
            return;
        }

        $factionName = $factionPlayer->getFaction();
        $faction = FactionManager::getInstance()->getFactionByName($factionName);


        if ($faction === null) return;

        $ev = new PlayerWarJoinEvent($sender, $factionName);
        $ev->call();
        if ($ev->isCancelled()) return;
    }
}
