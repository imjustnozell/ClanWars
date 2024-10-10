<?php

declare(strict_types=1);

namespace Nozell\ClanWar;

use pocketmine\plugin\PluginBase;
use Nozell\ClanWar\factory\WarFactory;
use Nozell\ClanWar\commands\WarCommand;
use Nozell\ClanWar\listeners\EventListener;
use CortexPE\Commando\PacketHooker;
use Nozell\ClanWar\utils\KickQueueManager;
use pocketmine\utils\SingletonTrait;

class Main extends PluginBase
{
    use SingletonTrait;

    private WarFactory $warFactory;
    private KickQueueManager $kickQueueManager;

    public function onEnable(): void
    {

        self::setInstance($this);

        $this->warFactory = new WarFactory();
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
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
    }


    private function registerCommands(): void
    {
        $this->getServer()->getCommandMap()->register("war", new WarCommand($this, "war", "Comienza la guerra de clanes o muestra información"));
    }


    public function getWarFactory(): WarFactory
    {
        return $this->warFactory;
    }

    public function getKickQueueManager(): KickQueueManager
    {
        return $this->kickQueueManager;
    }
}
