<?php

namespace Nozell\ClanWar\placeholders;

use Nozell\ClanWar\clan\ClanManager;
use Nozell\ClanWar\sessions\SessionWarManager;
use Nozell\PlaceholderAPI\placeholders\PlayerPlaceholder;
use pocketmine\player\Player;

class MembersCount extends PlayerPlaceholder
{
    public function getIdentifier(): string
    {
        return "clanwar_member";
    }

    protected function processPlayer(Player $player): string
    {
        $session = SessionWarManager::getInstance()->getPlayerSession($player);
        $members = "";
        if ($session->isParticipant()) {
            $clan = ClanManager::getInstance()->getClan($session->getClanName());
            $members = $clan->getPlayerCount();
        }
        return $members;
    }
}
