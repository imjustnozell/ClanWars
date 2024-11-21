<?php

namespace Nozell\ClanWar\interfaces;

use pocketmine\player\Player;

interface ClanWarEventInterface
{
    public function getPlayer(): Player;
    public function getClanName(): string;
}
