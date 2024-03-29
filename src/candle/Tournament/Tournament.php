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
            "BuildUHC" => Kit::BuildUHC($player),
            default => "Kit doesnt exist"
        };
    }

    public function loadArena(Player $player, string $tournament): void {
        match ($tournament) {
            "RedRover" => Server::getInstance()->getWorldManager()->loadWorld(loader::getInstance()->getConfig()->getNested("TournamentWorlds.RedRover.world")),
            "Sumo" => Server::getInstance()->getWorldManager()->loadWorld( loader::getInstance()->getConfig()->getNested("TournamentWorlds.Sumo.world")),
            "BuildUHC" => Server::getInstance()->getWorldManager()->loadWorld(loader::getInstance()->getConfig()->getNested("TournamentWorlds.BuildUHC.world")),
            default => "world doesnt exist"
        };
    }

    public function TeleportArena(Player $player, string $tournament): void {
        $config = loader::getInstance()->getConfig();
        match ($tournament) {
            "RedRover" => $player->teleport(new Position($config->getNested("TournamentWorlds.RedRover.x"),$config->getNested("TournamentWorlds.RedRover.y"),$config->getNested("TournamentWorlds.RedRover.z"), Server::getInstance()->getWorldManager()->getWorldByName($config->getNested("TournamentWorlds.RedRover.world")))),
            "Sumo" => $player->teleport(new Position($config->getNested("TournamentWorlds.Sumo.x"),$config->getNested("TournamentWorlds.Sumo.y"),$config->getNested("TournamentWorlds.Sumo.z"), Server::getInstance()->getWorldManager()->getWorldByName($config->getNested("TournamentWorlds.Sumo.world")))),
            "BuildUHC" => $player->teleport(new Position($config->getNested("TournamentWorlds.BuildUHC.x"),$config->getNested("TournamentWorlds.BuildUHC.y"),$config->getNested("TournamentWorlds.BuildUHC.z"), Server::getInstance()->getWorldManager()->getWorldByName($config->getNested("TournamentWorlds.BuildUHC.world")))),
            default => "World Doesnt exists/loaded"
        };
    }

    public function TeleportSpawn(Player $player, string $tournament): void {
        $config = loader::getInstance()->getConfig();
        match ($tournament) {
            "RedRover", "Sumo", "BuildUHC" => $player->teleport(new Position($config->getNested("TournamentWorlds.Spawn.x"),$config->getNested("TournamentWorlds.Spawn.y"),$config->getNested("TournamentWorlds.Spawn.z"), Server::getInstance()->getWorldManager()->getWorldByName($config->getNested("TournamentWorlds.Spawn.world")))),
            default => ""
        };
    }

    public function AnnounceTournament(string $tournament): void {
        match ($tournament) {
            "RedRover" => Server::getInstance()->broadcastMessage(loader::getInstance()->getConfig()->get("prefix") . loader::getInstance()->getConfig()->getNested("messages.RedRover.StartTournament")),
            "Sumo" => Server::getInstance()->broadcastMessage(loader::getInstance()->getConfig()->get("prefix")  . loader::getInstance()->getConfig()->getNested("messages.Sumo.StartTournament")),
            "BuildUHC" => Server::getInstance()->broadcastMessage(loader::getInstance()->getConfig()->get("prefix")  . loader::getInstance()->getConfig()->getNested("messages.BuildUHC.StartTournament")),
            default => ""
        };
    }

    public function AnnouncePlayerJoined(Player $player, string $tournament): void {
        match ($tournament) {
            "RedRover" => Server::getInstance()->broadcastMessage(loader::getInstance()->getConfig()->get("prefix") . $player->getName() . loader::getInstance()->getConfig()->getNested("messages.RedRover.PlayerJoinTournament")),
            "Sumo" => Server::getInstance()->broadcastMessage(loader::getInstance()->getConfig()->get("prefix") . $player->getName() . loader::getInstance()->getConfig()->getNested("messages.Sumo.PlayerJoinTournament")),
            "BuildUHC" => Server::getInstance()->broadcastMessage(loader::getInstance()->getConfig()->get("prefix") . $player->getName() . loader::getInstance()->getConfig()->getNested("messages.BuildUHC.PlayerJoinTournament")),
            default => ""
        };
    }

    public function AnnounceTournamentStarted(string $tournament): void {
        match ($tournament) {
            "RedRover" => Server::getInstance()->broadcastMessage(loader::getInstance()->getConfig()->get("prefix") . loader::getInstance()->getConfig()->getNested("messages.RedRover.AnnounceTournamentStarted")),
            "Sumo" => Server::getInstance()->broadcastMessage(loader::getInstance()->getConfig()->get("prefix") . loader::getInstance()->getConfig()->getNested("messages.Sumo.AnnounceTournamentStarted")),
            "BuildUHC" => Server::getInstance()->broadcastMessage(loader::getInstance()->getConfig()->get("prefix") . loader::getInstance()->getConfig()->getNested("messages.BuildUHC.AnnounceTournamentStarted")),
            default => ""
        };
    }

    public function AnnouncePlayerLeft(Player $player, string $tournament): void {
        match ($tournament) {
            "RedRover" => $player->sendMessage(loader::getInstance()->getConfig()->get("prefix") . loader::getInstance()->getConfig()->getNested("messages.RedRover.PlayerLeaveTournament")),
            "Sumo" => $player->sendMessage(loader::getInstance()->getConfig()->get("prefix") . loader::getInstance()->getConfig()->getNested("messages.Sumo.PlayerLeaveTournament")),
            "BuildUHC" => $player->sendMessage(loader::getInstance()->getConfig()->get("prefix") . loader::getInstance()->getConfig()->getNested("messages.BuildUHC.PlayerLeaveTournament")),
            default => ""
        };
    }

    #not really sure why i made this but ill see later .-.
//    public function AnnouncePlayerSpectate(Player $player, string $tournament): void {
//        match ($tournament) {
//            "RedRover" => $player->sendMessage(loader::getInstance()->getConfig()->get("prefix") . loader::getInstance()->getConfig()->getNested("messages.RedRover.SpectateTournament")),
//            "Sumo" => $player->sendMessage(loader::getInstance()->getConfig()->get("prefix") . loader::getInstance()->getConfig()->getNested("messages.Sumo.SpectateTournament")),
//            "BuildUHC" => $player->sendMessage(loader::getInstance()->getConfig()->get("prefix") . loader::getInstance()->getConfig()->getNested("messages.BuildUHC.SpectateTournament")),
//            default => ""
//        };
//    }



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
            $player->sendMessage(loader::getInstance()->getConfig()->get("prefix") . "You've joined the " . TextFormat::BLUE . "Blue" . TextFormat::RESET . " team.");
        } else {
            $this->redTeam[$player->getName()] = $player;
            $player->sendMessage(loader::getInstance()->getConfig()->get("prefix") . "You've joined the " . TextFormat::RED . "Red" . TextFormat::RESET . " team.");
        }
    }

    public function kickTeam(Player $player): void
    {
        if ($this->getTeam($player) === "Blue") {
            unset($this->blueTeam[$player->getName()]);
        }
        if ($this->getTeam($player) === "Red") {
            unset($this->redTeam[$player->getName()]);
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