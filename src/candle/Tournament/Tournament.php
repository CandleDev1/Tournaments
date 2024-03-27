<?php

namespace candle\Tournament;

use candle\loader;
use candle\Tournament\Kits\Kit;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;

abstract class Tournament {

    public array $redTeam = [];
    public array $blueTeam = [];

    public function setKit(Player $player, string $tournament): void {
        match ($tournament) {
            "RedRover" => Kit::RedRoverKit($player),
            "Sumo" => Kit::Sumo($player),
            "backlobby" => Kit::BackLobby($player),
            default => "Kit doesnt exist"
        };
    }

    public function loadArena(Player $player, string $tournament): void {
        match ($tournament) {
            "RedRover" => Server::getInstance()->getWorldManager()->loadWorld("lunar_spawn"),
            "Sumo" => Server::getInstance()->getWorldManager()->loadWorld("mine"),
            default => "world doesnt exist"
        };
    }

    public function TeleportArena(Player $player, string $tournament): void {
        match ($tournament) {
            "RedRover" => $player->teleport(new Position(0,99,0, Server::getInstance()->getWorldManager()->getWorldByName("lunar_spawn"))),
            "Sumo" => $player->teleport(new Position(0,90,0, Server::getInstance()->getWorldManager()->getWorldByName("mine"))),
            default => "World Doesnt exists/loaded"
        };
    }

    public function TeleportSpawn(Player $player, string $tournament): void {
        match ($tournament) {
            "RedRover" => $player->teleport(new Position(120,120,120, Server::getInstance()->getWorldManager()->getWorldByName("world"))),
            "Sumo" => $player->teleport(new Position(120,120,120, Server::getInstance()->getWorldManager()->getWorldByName("world")))
        };
    }

    public function AnnounceTournament(string $tournament): void {
        match ($tournament) {
            "RedRover" => Server::getInstance()->broadcastMessage(loader::PREFIX . "RedRover event has begun use /tournament join RedRover to enter the Tournament!")
        };
    }

    public function AnnouncePlayerJoined(Player $player, string $tournament): void {
        match ($tournament) {
            "RedRover" => Server::getInstance()->broadcastMessage(loader::PREFIX . $player->getName() . " has joined the RedRover Tournament!"),
            "Sumo" => Server::getInstance()->broadcastMessage(loader::PREFIX . $player->getName() . " has joined the Sumo Tournament!"),
        };
    }

    public function AnnounceTournamentStarted(string $tournament): void {
        match ($tournament) {
            "RedRover" => Server::getInstance()->broadcastMessage(loader::PREFIX . "RedRover Tournament has started use /tournament spectate"),
            "Sumo" => Server::getInstance()->broadcastMessage(loader::PREFIX . "Sumo Tournament has started use /tournament spectate"),
            default => ""
        };
    }

    public function AnnouncePlayerLeft(Player $player, string $tournament): void {
        match ($tournament) {
            "RedRover" => $player->sendMessage(loader::PREFIX . "You have left the RedRover Tournament"),
            "Sumo" => $player->sendMessage(loader::PREFIX . "You have left the Sumo Tournament")
        };
    }

    public function AnnouncePlayerSpectate(Player $player, string $tournament): void {
        match ($tournament) {
            "RedRover" => $player->sendMessage(loader::PREFIX . "You have started spectating the Tournament RedRover")
        };
    }



    public function setTeam(Player $player, bool $force = false, ?string $team = null): void
    {
        if ($force and $team !== null) {
            if ($team === "blue") {
                $this->blueTeam[$player->getName()] = $player;
            }
            if ($team === "red") {
                $this->redTeam[$player->getName()] = $player;
            }
            return;
        }


        if(count($this->blueTeam) < count($this->redTeam)) {
            $this->blueTeam[$player->getName()] = $player;
            $player->sendMessage(loader::PREFIX . "You've joined the " . TextFormat::BLUE . "Blue" . TextFormat::RESET . " team.");
        } else {
            $this->redTeam[$player->getName()] = $player;
            $player->sendMessage(loader::PREFIX . "You've joined the " . TextFormat::RED . "Red" . TextFormat::RESET . " team.");
        }
    }

    public function getTeam(Player $player): string
    {
        $team = "N/A";
        if (isset($this->blueTeam[$player->getName()])) {
            $team = "Blue";
        }
        if (isset($this->redTeam[$player->getName()])) {
            $team = "Red";
        }
        return $team;
    }



}