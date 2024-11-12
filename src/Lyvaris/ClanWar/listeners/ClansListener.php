<?php

declare(strict_types=1);

namespace Lyvaris\ClanWar\listeners;

use Lyvaris\ClanWar\clan\ClanManager;
use Lyvaris\ClanWar\events\ClanCreateEvent;
use Lyvaris\ClanWar\events\ClanEliminateEvent;
use Lyvaris\ClanWar\utils\WarUtils;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat as TF;

class ClansListener implements Listener
{

    public function ClanJoin(ClanCreateEvent $event): void
    {
        $clanName = $event->getClanName();
        $clan = ClanManager::getInstance();
        if (!$clan->clanExists($clanName)) {
            $clan->createClan($clanName);
        }
    }

    public function ClanRemove(ClanEliminateEvent $event): void
    {
        $clanName = $event->getClanName();
        $clan = ClanManager::getInstance();

        if ($clan->clanExists($clanName)) {
            $clan->removeClan($clanName);
            WarUtils::getInstance()->broadcastMessage(TF::RED . "El clan $clanName ha sido eliminado.");
        }
    }
}
