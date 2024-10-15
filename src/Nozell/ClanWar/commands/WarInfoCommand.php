<?php

declare(strict_types=1);

namespace Nozell\ClanWar\commands;

use CortexPE\Commando\BaseSubCommand;
use Nozell\ClanWar\clan\ClanManager;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use Nozell\ClanWar\Main;
use Nozell\ClanWar\utils\Perms;
use Nozell\ClanWar\utils\WarState;

class WarInfoCommand extends BaseSubCommand
{

    protected function prepare(): void
    {
        $this->setPermission(Perms::Default);
    }

    public function onRun(CommandSender $sender, string $label, array $args): void
    {
        $main = Main::getInstance();
        $warState = WarState::getInstance();

        if (!$warState->isWarActive()) {
            $sender->sendMessage(TF::RED . "No hay ninguna guerra activa en este momento.");
            return;
        }

        $timeElapsed = $warState->getTimeElapsed();
        $clans = ClanManager::getInstance()->getAllClans();

        $sender->sendMessage(TF::GREEN . "Tiempo transcurrido: " . gmdate("i:s", $timeElapsed));
        $sender->sendMessage(TF::GREEN . "Clanes vivos: " . count($clans));

        foreach ($clans as $clanName => $clan) {
            $aliveMembers = ClanManager::getInstance()->getClan($clanName)->getPlayerCount();
            $sender->sendMessage(TF::YELLOW . "Clan: " . TF::GOLD . $clanName . TF::YELLOW . " | Miembros vivos: " . TF::GOLD . $aliveMembers);
        }
    }
}
