<?php

namespace Nozell\ClanWar\events;

use pocketmine\event\Event;
use pocketmine\player\Player;
use Nozell\ClanWar\interfaces\ClanWarEventInterface;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;

class PlayerWarJoinEvent extends Event implements ClanWarEventInterface, Cancellable
{
    use CancellableTrait;
    private Player $player;
    private string $clanName;

    public function __construct(Player $player, string $clanName)
    {
        $this->player = $player;
        $this->clanName = $clanName;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getClanName(): string
    {
        return $this->clanName;
    }
}
