<?php

namespace Nozell\ClanWar\placeholders;

use Nozell\ClanWar\sessions\SessionWarManager;
use Nozell\PlaceholderAPI\placeholders\PlayerPlaceholder;
use pocketmine\player\Player;

class WarClanName extends PlayerPlaceholder
{
    public function getIdentifier(): string
    {
        return "warclan_name";
    }

    protected function processPlayer(Player $player): string
    {
        $session = SessionWarManager::getInstance()->getPlayerSession($player);
        $name = "";
        if ($session->isParticipant()) {
            $name = $session->getClanName();
        }
        return $name;
    }
}
