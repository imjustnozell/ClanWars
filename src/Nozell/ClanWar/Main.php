<?php

declare(strict_types=1);

namespace Nozell\ClanWar;

use pocketmine\plugin\PluginBase;
use Nozell\ClanWar\factory\WarFactory;
use Nozell\ClanWar\commands\WarCommand;
use Nozell\ClanWar\listeners\EventListener;
use Nozell\ClanWar\tasks\WarStartTask;
use Nozell\ClanWar\tasks\WarTask;
use CortexPE\Commando\PacketHooker;
use pocketmine\utils\SingletonTrait;

class Main extends PluginBase
{
    use SingletonTrait;

    private WarFactory $warFactory;

    public function onEnable(): void
    {

        self::setInstance($this);

        $this->warFactory = new WarFactory();

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


    public function startWarCountdown(int $seconds): void
    {

        $this->getScheduler()->scheduleRepeatingTask(new WarStartTask($seconds), 20);
    }


    public function startWarTask(): void
    {

        $this->getScheduler()->scheduleRepeatingTask(new WarTask(), 20 * 60);
    }
}
