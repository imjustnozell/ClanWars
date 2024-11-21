<?php

declare(strict_types=1);

namespace Nozell\ClanWar;

use pocketmine\plugin\PluginBase;
use Nozell\ClanWar\commands\WarCommand;
use Nozell\ClanWar\listeners\EventListener;
use CortexPE\Commando\PacketHooker;
use Nozell\ClanWar\listeners\ClansListener;
use Nozell\ClanWar\listeners\WarListener;
use Nozell\ClanWar\placeholders\ClansAlive;
use Nozell\ClanWar\placeholders\MembersCount;
use Nozell\ClanWar\placeholders\WarClanName;
use Nozell\ClanWar\placeholders\WarTimeLapsed;
use Nozell\ClanWar\utils\KickQueueManager;
use Nozell\PlaceholderAPI\PlaceholderAPI;
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
    private function registerPlaceholders(): void
    {
        $placeholders = [
            new ClansAlive(),
            new MembersCount(),
            new WarClanName(),
            new WarTimeLapsed()
        ];
        foreach ($placeholders as $placeholder) {
            PlaceholderAPI::getRegistry()->registerPlaceholder($placeholder);
        }
    }
}
