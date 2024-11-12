<?php

declare(strict_types=1);

namespace Nozell\ClanWar\tasks;

use Nozell\ClanWar\clan\ClanManager;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as TF;
use Nozell\ClanWar\Main;
use Nozell\ClanWar\sessions\SessionManager;
use Nozell\ClanWar\utils\ClanUtils;
use Nozell\ClanWar\utils\WarState;
use Nozell\ClanWar\utils\WarUtils;
use pocketmine\Server;
use pocketmine\world\particle\HappyVillagerParticle;
use pocketmine\world\particle\HugeExplodeParticle;
use pocketmine\world\Position;
use pocketmine\world\sound\AnvilFallSound;
use pocketmine\world\sound\PopSound;
use pocketmine\world\sound\XpCollectSound;
use pocketmine\world\World;

class WarCelebrate extends Task
{
    private $count = 0;
    private $world;
    private $position;

    public function __construct(World $world, Position $position)
    {
        $this->world = $world;
        $this->position = $position;
    }

    public function onRun(): void
    {

        $this->world->addParticle($this->position, new HugeExplodeParticle());
        $this->world->addParticle($this->position, new HappyVillagerParticle());
        $this->world->addSound($this->position, new PopSound());
        $this->world->addSound($this->position, new AnvilFallSound());


        $this->count++;
        if ($this->count >= 5) {
            $this->getHandler()?->cancel();
        }
    }
}
