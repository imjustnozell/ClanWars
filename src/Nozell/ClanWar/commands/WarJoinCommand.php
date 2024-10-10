<?php

declare(strict_types=1);

namespace Nozell\ClanWar\commands;

use CortexPE\Commando\BaseSubCommand;
use Nozell\ClanWar\sessions\PlayerSession;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use Nozell\ClanWar\Main;
use Nozell\ClanWar\utils\ClanUtils;
use rxduz\factions\player\FactionPlayer;
use rxduz\factions\player\PlayerManager;
use rxduz\factions\faction\FactionManager;

class WarJoinCommand extends BaseSubCommand
{
    protected function prepare(): void
    {
        $this->setPermission("clanwar.command.join");
    }

    public function onRun(CommandSender $sender, string $label, array $args): void
    {
        $main = Main::getInstance();

        if (!$sender instanceof Player) {
            $sender->sendMessage(TF::RED . "Este comando solo puede ser usado por jugadores.");
            return;
        }

        if (!$main->getWarFactory()->isWarWaiting()) {
            $sender->sendMessage(TF::RED . "No puedes unirte a la guerra porque ya ha comenzado o no está en estado de espera.");
            return;
        }

        $session = $main->getWarFactory()->getPlayerSession($sender);

        if ($session !== null && $session->isParticipant()) {
            $sender->sendMessage(TF::RED . "Ya estás participando en la guerra.");
            return;
        }

        $factionPlayer = PlayerManager::getInstance()->getSessionByName($sender->getName());

        if (!$factionPlayer instanceof FactionPlayer || !$factionPlayer->inFaction()) {
            $sender->sendMessage(TF::RED . "No puedes unirte a la guerra si no perteneces a una facción.");
            return;
        }

        $factionName = $factionPlayer->getFaction();
        $faction = FactionManager::getInstance()->getFactionByName($factionName);

        if ($faction === null) {
            $sender->sendMessage(TF::RED . "Hubo un error al intentar obtener tu facción. Inténtalo de nuevo.");
            return;
        }

        $clanMembers = $main->getWarFactory()->getClans()[$factionName] ?? [];
        if (count($clanMembers) >= ClanUtils::HeightMembers) {
            $sender->sendMessage(TF::RED . "Tu facción ya tiene" . ClanUtils::HeightMembers . "miembros en la guerra, no puedes unirte.");
            return;
        }

        if (isset($main->getWarFactory()->getClans()[$factionName])) {
            $main->getWarFactory()->addClan($factionName, $sender);
            $sender->sendMessage(TF::GREEN . "Te has unido a la guerra como miembro de la facción $factionName.");
        } else {

            $main->getWarFactory()->addClan($factionName, $sender);
            $sender->sendMessage(TF::GREEN . "Has creado y te has unido al clan de la facción $factionName.");
        }

        $newSession = new PlayerSession($sender);
        $newSession->setClanName($factionName);
        $main->getWarFactory()->addPlayerSession($sender, $newSession);
        $main->getWarFactory()->sendPlayerToArena($sender);
    }
}
