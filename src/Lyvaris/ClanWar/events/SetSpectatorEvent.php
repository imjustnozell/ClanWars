<?php

declare(strict_types=1);

namespace Lyvaris\ClanWar\events;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use pocketmine\player\Player;

class SetSpectatorEvent extends Event implements Cancellable
{
    use CancellableTrait;
    private Player $player;

    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }
}
