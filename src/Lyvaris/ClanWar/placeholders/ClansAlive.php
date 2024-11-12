<?php

namespace Lyvaris\ClanWar\placeholders;

use Lyvaris\ClanWar\clan\ClanManager;
use Nozell\PlaceholderAPI\placeholders\ServerPlaceholder;

class ClansAlive  extends ServerPlaceholder
{
    public function getIdentifier(): string
    {
        return "clanwars_alive";
    }

    protected function processServer(): string
    {
        $clan = ClanManager::getInstance()->getAllClans();
        return count($clan);
    }
}
