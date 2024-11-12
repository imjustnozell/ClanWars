<?php

declare(strict_types=1);

namespace Lyvaris\ClanWar;

use pocketmine\plugin\PluginBase;
use Lyvaris\ClanWar\commands\WarCommand;
use Lyvaris\ClanWar\listeners\EventListener;
use CortexPE\Commando\PacketHooker;
use Lyvaris\ClanWar\listeners\ClansListener;
use Lyvaris\ClanWar\listeners\WarListener;
use Lyvaris\ClanWar\utils\KickQueueManager;
use pocketmine\utils\SingletonTrait;

class Main extends PluginBase
{
    use SingletonTrait;

    private KickQueueManager $kickQueueManager;

    public function onEnable(): void
    {

        self::setInstance($this);

        $this->kickQueueManager = new KickQueueManager();

        if (!PacketHooker::isRegistered()) {
            PacketHooker::register($this);
        }

        $this->registerListeners();

        $this->registerCommands();

        $this->getLogger()->info("Clan War Plugin enabled.");
    }


    private function registerListeners(): void
    {
        $events = [new EventListener(), new ClansListener(), new WarListener()];
        foreach ($events as $event) {
            $this->getServer()->getPluginManager()->registerEvents($event, $this);
        }
    }


    private function registerCommands(): void
    {
        $this->getServer()->getCommandMap()->register("war", new WarCommand($this, "war", "Comienza la guerra de clanes o muestra información"));
    }

    public function getKickQueueManager(): KickQueueManager
    {
        return $this->kickQueueManager;
    }
}
