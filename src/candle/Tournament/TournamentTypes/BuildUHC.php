<?php

namespace candle\Tournament\TournamentTypes;

use AllowDynamicProperties;
use candle\loader;
use candle\Scoreboard\ScoreboardManager;
use candle\Session\SessionFactory;
use candle\Tournament\Tournament;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\sound\XpCollectSound;
use pocketmine\math\Vector3;

#[AllowDynamicProperties] class BuildUHC extends Tournament
{

    const int idle = 0;
    const int waiting = 1;
    const int countdown = 2;
    const int playing = 3;

    public bool $fight = false;
    public int $state = BuildUHC::idle;

    public int $countdown = 10;

    /** @var Player[] */
    public array $players = [];

    public function setUpArena(Player $player): void {
        $this->loadArena($player, "BuildUHC");
        $this->AnnounceTournament("BuildUHC");
        $this->state = BuildUHC::waiting;
    }

    public function StartTournament(Player $player, int $minPlayers,int $maxplayers, int $rewardID): void
    {
        if($minPlayers > $maxplayers) {
            $player->sendMessage(loader::PREFIX . "Minimum players cannot be higher than maximum players!");
            return;
        }
        if($this->state === BuildUHC::playing || $this->state === BuildUHC::countdown || $this->state === BuildUHC::waiting) {
            $player->sendMessage(loader::PREFIX . "Theres already a Tournament running");
            return;
        }

        $this->minPlayers = $minPlayers;
        $this->maxPlayers = $maxplayers;

        $this->state = BuildUHC::waiting;

        switch($rewardID) {
            case 1:

                break;
            case 2:

                break;
            default:

        }

        $this->setupArena($player);
    }

    public function HandlePlayerJoin(Player $player): void {
        if($this->state === BuildUHC::playing) {
            $player->sendMessage(loader::PREFIX . "The BuildUHC event has already begun.");
            return;
        }

        $this->players[] = $player;
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();

        $this->TeleportArena($player, "BuildUHC");
        $this->setTeam($player);
        $this->AnnouncePlayerJoined($player, "BuildUHC");
        $this->setKit($player, "backlobby");
        SessionFactory::getSession($player)->setInTournament(true, "BuildUHC");
    }

    public function HandlePlayerLeave(Player $player): void {
        $this->TeleportSpawn($player, "BuildUHC");
        $this->AnnouncePlayerLeft($player, "BuildUHC");
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getInventory()->setItem(4, VanillaItems::EMERALD()->setCustomName("§bTournamenet §f(right click to use)"));
        ScoreboardManager::remove($player);
        SessionFactory::getSession($player)->setInTournament(false, "BuildUHC");

        $player->setGamemode(GameMode::SURVIVAL);
        unset($this->players[array_search($player, $this->players, true)]);
    }

    public function HandleSpectators(Player $player): void {
        $this->TeleportArena($player, "BuildUHC");
        $player->setGamemode(GameMode::SPECTATOR);
        SessionFactory::getSession($player)->setInTournament(true, "BuildUHC");
    }





    public function Fighting(): void {
        if($this->state !== BuildUHC::playing) {
            return;
        }

        $this->fight = true;
        foreach ($this->players as $player) {
            $player->getInventory()->clearAll();
            $this->setKit($player, "BuildUHC");
            $this->AnnounceTournamentStarted("BuildUHC");
            if(isset($this->blueTeam[$player->getName()])) {
                $player->teleport(new Position(loader::getInstance()->getConfig()->getNested("TournamentWorlds.BuildUHC1.x"), loader::getInstance()->getConfig()->getNested("TournamentWorlds.BuildUHC1.y"), loader::getInstance()->getConfig()->getNested("TournamentWorlds.BuildUHC1.z"), Server::getInstance()->getWorldManager()->getWorldByName(loader::getInstance()->getConfig()->getNested("TournamentWorlds.BuildUHC1.world"))));
            } elseif(isset($this->redTeam[$player->getName()])) {
                $player->teleport(new Position(loader::getInstance()->getConfig()->getNested("TournamentWorlds.BuildUHC2.x"), loader::getInstance()->getConfig()->getNested("TournamentWorlds.BuildUHC2.y"), loader::getInstance()->getConfig()->getNested("TournamentWorlds.BuildUHC2.z"), Server::getInstance()->getWorldManager()->getWorldByName(loader::getInstance()->getConfig()->getNested("TournamentWorlds.BuildUHC2.world"))));
            }
        }
    }

    public function tick(): void
    {
        $this->sendScore();
        switch ($this->state) {
            case BuildUHC::waiting:
                if (count($this->players) > 1 and count($this->redTeam) >= 1 and count($this->blueTeam) >= 1) {
                    $this->state = BuildUHC::countdown;
                }
                break;
            case BuildUHC::countdown;
                if (count($this->players) <= 1 or count($this->redTeam) < 1 or count($this->blueTeam) < 1) {
                    $this->state = BuildUHC::waiting;
                    return;
                }
                if ($this->countdown > 0) {
                    $c = $this->countdown--;
                    foreach ($this->players as $player) {
                        $player->sendTitle(TextFormat::MINECOIN_GOLD . gmdate("i:s", $c));
                        $player->getWorld()->addSound(new Vector3($player->getPosition()->getX(), $player->getPosition()->getY(), $player->getPosition()->getZ()), new XpCollectSound());
                    }
                    $this->fight = false;
                    return;
                }
                $this->state = BuildUHC::playing;
                break;
            case BuildUHC::playing:
                if(!$this->fight) {
                    $this->Fighting();
                } elseif($this->players > 1) {
                    foreach ($this->players as $player) {
                        $this->HandlePlayerLeave($player);
                        $this->state = Sumo::idle;
                        $this->countdown = 10;
                        $this->StopTournament();
                    }
                }
        }
    }


    public function sendScore(): void
    {
        $states = [BuildUHC::idle => "Idle", BuildUHC::waiting => "Waiting", BuildUHC::countdown => "Countdown", BuildUHC::playing => "Running"];
        foreach ($this->players as $player) {
            switch ($this->state) {
                case BuildUHC::waiting:
                    ScoreboardManager::new($player, "1", "§c§bTournaments");
                    ScoreboardManager::setLine($player, 1, '');
                    ScoreboardManager::setLine($player, 2, "§bTournament: " . TextFormat::WHITE . "BuildUHC");
                    ScoreboardManager::setLine($player, 3, "§bPlayers: " . TextFormat::WHITE . count($this->players));
                    ScoreboardManager::setLine($player, 4, "§bStatus: " . TextFormat::WHITE . $states[$this->state]);
                    ScoreboardManager::setLine($player, 5, "§f");
                    break;
                case BuildUHC::countdown:
                    ScoreboardManager::new($player, "2","§c§bTournaments");
                    ScoreboardManager::setLine($player, 1, '');
                    ScoreboardManager::setLine($player, 2, "§bTournament: " . TextFormat::WHITE . "(BuildUHC)");
                    ScoreboardManager::setLine($player, 3, "§bPlayers: " . TextFormat::WHITE . count($this->players));
                    ScoreboardManager::setLine($player, 4, "§bStarting in: " . TextFormat::WHITE . gmdate("i:s", $this->countdown));
                    ScoreboardManager::setLine($player, 5, "§f");
                    break;
                case BuildUHC::playing:
                    ScoreboardManager::new($player, "3", "§c§bTournaments");
                    ScoreboardManager::setLine($player, 1, TextFormat::GRAY . '');
                    ScoreboardManager::setLine($player, 2, "§bEvent " . TextFormat::WHITE . "(BuildUHC)");
                    ScoreboardManager::setLine($player, 3, "§bPlayers: " . TextFormat::WHITE . count($this->players));
//                    ScoreboardManager::setLine($player, 4, "§bTeam: " . TextFormat::WHITE . $this->getTeam($player));
                    ScoreboardManager::setLine($player, 4, "§f");
            }
        }
    }



    public function StopTournament(): void {
        foreach ($this->players as $player) {
            $this->HandlePlayerLeave($player);
        }
        $this->players = [];
        $this->state = BuildUHC::idle;
        $this->countdown = 10;
        $this->fight = false;
        $this->redTeam = [];
        $this->blueTeam = [];
        Server::getInstance()->getWorldManager()->unloadWorld(loader::getInstance()->getConfig()->getNested("TournamentWorlds.BuildUHC.world"));
    }

}