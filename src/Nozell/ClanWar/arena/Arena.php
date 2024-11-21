<?php

namespace Nozell\ClanWar\arena;

use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat as TF;

class Arena
{
    use SingletonTrait;
    private ?string $arenaCoordinates = null;
    private const ARENA_FILE = "arena.json";

    public function __construct()
    {
        $this->loadArena();
    }

    public function loadArena(): void
    {
        $filePath = Server::getInstance()->getDataPath() . self::ARENA_FILE;

        if (file_exists($filePath)) {
            $data = json_decode(file_get_contents($filePath), true);
            if (isset($data['arena'])) {
                $this->arenaCoordinates = $data['arena'];
            }
        }
    }

    public function saveArena(): void
    {
        $filePath = Server::getInstance()->getDataPath() . self::ARENA_FILE;

        $data = [
            'arena' => $this->arenaCoordinates
        ];

        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function setArena(string $coordinates): void
    {
        $this->arenaCoordinates = $coordinates;
        $this->saveArena();
    }

    public function getArena(): ?string
    {
        return $this->arenaCoordinates;
    }

    public function sendPlayerToArena(Player $player): void
    {
        if ($this->arenaCoordinates !== null) {
            $coords = explode(",", $this->arenaCoordinates);
            $player->teleport(new Position((float) $coords[0], (float) $coords[1], (float) $coords[2], $player->getWorld()));
            $player->sendMessage(TF::GREEN . "Has sido enviado a la arena.");
        } else {
            $player->sendMessage(TF::RED . "¡La arena no está configurada!");
        }
    }
}
