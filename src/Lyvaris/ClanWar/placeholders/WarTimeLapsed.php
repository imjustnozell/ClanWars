<?php

namespace Nozell\ClanWar\placeholders;

use Nozell\ClanWar\utils\WarState;
use Nozell\PlaceholderAPI\placeholders\ServerPlaceholder;

class WarTimeLapsed  extends ServerPlaceholder
{
    public function getIdentifier(): string
    {
        return "wartime_lapsed";
    }

    protected function processServer(): string
    {
        $timeLapsed = WarState::getInstance()->getTimeElapsed();
        return gmdate("i:s", $timeLapsed);
    }
}
