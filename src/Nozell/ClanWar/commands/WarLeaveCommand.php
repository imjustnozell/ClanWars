<?php

declare(strict_types=1);

namespace Nozell\ClanWar\commands;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use Nozell\ClanWar\Main;

class WarLeaveCommand extends BaseSubCommand
{
    protected function prepare(): void
    {
        $this->setPermission("clanwar.command.leave");
    }

    public function onRun(CommandSender $sender, string $label, array $args): void
    {
        $main = Main::getInstance();

        if (!$sender instanceof Player) {
            $sender->sendMessage(TF::RED . "Este comando solo puede ser usado por jugadores.");
            return;
        }

        $session = $main->getWarFactory()->getPlayerSession($sender);

        if ($session === null || !$session->isParticipant()) {
            $sender->sendMessage(TF::RED . "No estás participando en ninguna guerra.");
            return;
        }

        $factionName = $session->getClanName();
        $clanMembers = $main->getWarFactory()->getClans()[$factionName] ?? [];
        
        if (count($clanMembers) === 1) {
            $main->getWarFactory()->removeClan($factionName);
            $sender->sendMessage(TF::RED . "Has salido de la guerra, y tu clan $factionName ha sido eliminado porque eras el único miembro.");
        } else {
            
            $main->getWarFactory()->removePlayer($sender);
            $sender->sendMessage(TF::YELLOW . "Has salido de la guerra y has sido removido de tu clan $factionName.");
        }

        $this->sendPlayerToLobby($sender);
    }

    /**
     * 
     * @param Player $player
     * @return void
     */
    private function sendPlayerToLobby(Player $player): void
    {
        $lobbyWorld = $player->getServer()->getWorldManager()->getDefaultWorld(); 
        $lobbyPosition = $lobbyWorld->getSafeSpawn(); 
        $player->teleport($lobbyPosition);
        $player->sendMessage(TF::GREEN . "Has sido teletransportado al lobby.");
    }
}