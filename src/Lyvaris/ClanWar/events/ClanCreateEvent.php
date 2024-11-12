<?php

declare(strict_types=1);

namespace Lyvaris\ClanWar\events;

use pocketmine\event\Event;

class ClanCreateEvent extends Event
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
