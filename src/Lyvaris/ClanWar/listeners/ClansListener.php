<?php

declare(strict_types=1);

namespace Nozell\ClanWar\listeners;

use Nozell\ClanWar\clan\ClanManager;
use Nozell\ClanWar\events\ClanCreateEvent;
use Nozell\ClanWar\events\ClanEliminateEvent;
use Nozell\ClanWar\utils\WarUtils;
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
