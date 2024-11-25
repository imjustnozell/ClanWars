<?php

declare(strict_types=1);

namespace Nozell\ClanWar\events;

use pocketmine\event\Event;

class ClanEliminateEvent extends Event
{
    private string $clanName;

    public function __construct(string $clanName)
    {
        $this->clanName = $clanName;
    }

    public function getClanName(): string
    {
        return $this->clanName;
    }
}
