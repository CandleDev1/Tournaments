<?php

namespace candle\Tournament\TournamentTypes;

use AllowDynamicProperties;
use candle\loader;
use candle\Scoreboard\ScoreboardManager;
use candle\Session\SessionFactory;
use candle\Tournament\Tournament;
use candle\TournamentPlayer;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;

#[AllowDynamicProperties] class Sumo extends Tournament
{

    const int idle = 0;
    const int waiting = 1;
    const int countdown = 2;
    const int playing = 3;
    const int Fighting = 4;

    public bool $fight = false;
    public int $state = Sumo::idle;

    public int $countdown = 10;

    public $fightcooldown = 3;

    /** @var Player[] */
    public array $players = [];

    public int $rounds = 0;

    public function setUpArena(Player $player): void {
        $this->loadArena($player, "Sumo");
        $this->AnnounceTournamentStarted("Sumo");
        $this->state = Sumo::waiting;
    }

    public function StartTournament(Player $player, int $minPlayers, int $maxPlayers, int $rewardID) {
        if($minPlayers > $maxPlayers) {
            $player->sendMessage(loader::PREFIX . "Minimum players cannot be higher than maximum players!");
            return;
        }

        if($this->state === Sumo::playing || $this->state === Sumo::countdown || $this->state === Sumo::waiting) {
            $player->sendMessage(loader::PREFIX . "Theres already a Tournament running");
            return;
        }

        $this->minPlayers = $minPlayers;
        $this->maxPlayers = $maxPlayers;

        $this->state = Sumo::waiting;

        switch ($rewardID) {
            case 1:

                break;
            case 2:

                break;
            default:

                break;
        }
        $this->setUpArena($player);
    }

    public function HandlePlayerJoin(Player $player): void {
        if($this->state === Sumo::playing) {
            $player->sendMessage(loader::PREFIX . "The Sumo event has already begun.");
            return;
        }
        $this->players[] = $player;
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $this->TeleportArena($player, "Sumo");
        $this->AnnouncePlayerJoined($player, "Sumo");
        $this->setKit($player, "backlobby");
        SessionFactory::getSession($player)->setInTournament(true, "Sumo");
    }

    public function HandlePlayerLeave(Player $player): void {
        $this->TeleportSpawn($player, "Sumo");
        $this->AnnouncePlayerLeft($player, "Sumo");
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getInventory()->setItem(4, VanillaItems::EMERALD()->setCustomName("§bTournamenet §f(right click to use)"));
        ScoreboardManager::remove($player);
        SessionFactory::getSession($player)->setInTournament(false, "Sumo");
        $player->setGamemode(GameMode::SURVIVAL);
        unset($this->players[array_search($player, $this->players, true)]);
    }

    public function HandleSpectators(Player $player): void {
        $this->TeleportArena($player, "Sumo");
        $player->setGamemode(GameMode::SPECTATOR);
        SessionFactory::getSession($player)->setInTournament(true, "Sumo");
    }




    public function Fighting(): void {
        if($this->state !== Sumo::playing) {
            return;
        }

        $this->fight = true;
        $config = loader::getInstance()->getConfig();
        shuffle($this->players);
        $RandomPlayer = array_slice($this->players, 0,2);


        $spawn = [new Position($config->getNested("TournamentWorlds.SumoPlayer1.x"), $config->getNested("TournamentWorlds.SumoPlayer1.y"), $config->getNested("TournamentWorlds.SumoPlayer1.z"), Server::getInstance()->getWorldManager()->getWorldByName($config->getNested("TournamentWorlds.SumoPlayer1.world"))), new Position($config->getNested("TournamentWorlds.SumoPlayer2.x"), $config->getNested("TournamentWorlds.SumoPlayer2.y"), $config->getNested("TournamentWorlds.SumoPlayer2.z"), Server::getInstance()->getWorldManager()->getWorldByName($config->getNested("TournamentWorlds.SumoPlayer2.world")))];
        foreach ($RandomPlayer as $index => $player) {
            $player->teleport($spawn[$index]);
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            $this->setKit($player, "Sumo");
            $this->AnnounceTournamentStarted("Sumo");
        }

    }



    public function tick(): void {
        $this->sendScore();
        switch ($this->state) {
            case Sumo::waiting:
                if(count($this->players) > 1) {
                    $this->state = Sumo::countdown;
                }
                break;
            case Sumo::countdown:
                if(count($this->players) <= 1) {
                    $this->state = Sumo::waiting;
                    return;
                }
                if($this->countdown > 0) {
                    $this->countdown--;
                    $this->fight = false;
                    return;
                }
                $this->state = Sumo::playing;
                break;
            case Sumo::playing:
                if (count($this->players) > 1) {
                    if(!$this->fight) {
                        $this->Fighting();
                    }
                } else {
                    foreach ($this->players as $player) {
                        $this->HandlePlayerLeave($player);
                        $this->state = Sumo::idle;
                        $this->countdown = 10;
                    }
                }
                if($this->fight) {
                    if($this->fightcooldown !== null and $this->fightcooldown > 0) {
                        foreach ($this->players as $player) {
                            $c = $this->fightcooldown--;
                            $player->sendTitle(TextFormat::GOLD . gmdate("i:s", $c));
                        }
                    } elseif ($this->fightcooldown !== null) {
                        foreach ($this->players as $player) {
                            $this->fightcooldown = null;
                        }
                    }
                }
                break;
        }
    }


    public function sendScore(): void
    {
        $states = [Sumo::idle => "Idle", Sumo::waiting => "Waiting", Sumo::countdown => "Countdown", Sumo::playing => "Running"];
        foreach ($this->players as $player) {
            switch ($this->state) {
                case RedRover::waiting:
                    ScoreboardManager::new($player, "1", "§c§bTournaments");
                    ScoreboardManager::setLine($player, 1, '');
                    ScoreboardManager::setLine($player, 2, "§bTournament: " . TextFormat::WHITE . "Sumo");
                    ScoreboardManager::setLine($player, 3, "§bPlayers: " . TextFormat::WHITE . count($this->players));
                    ScoreboardManager::setLine($player, 4, "§bStatus: " . TextFormat::WHITE . $states[$this->state]);
                    ScoreboardManager::setLine($player, 5, "§f");
                    break;
                case RedRover::countdown:
                    ScoreboardManager::new($player, "2","§c§bTournaments");
                    ScoreboardManager::setLine($player, 1, '');
                    ScoreboardManager::setLine($player, 2, "§bTournament: " . TextFormat::WHITE . "(Sumo)");
                    ScoreboardManager::setLine($player, 3, "§bPlayers: " . TextFormat::WHITE . count($this->players));
                    ScoreboardManager::setLine($player, 4, "§bStarting in: " . TextFormat::WHITE . gmdate("i:s", $this->countdown));
                    ScoreboardManager::setLine($player, 5, "§f");
                    break;
                case RedRover::playing:
                    ScoreboardManager::new($player, "3", "§c§bTournaments");
                    ScoreboardManager::setLine($player, 1, TextFormat::GRAY . '');
                    ScoreboardManager::setLine($player, 2, "§bEvent " . TextFormat::WHITE . "(Sumo)");
                    ScoreboardManager::setLine($player, 3, "§bPlayers: " . TextFormat::WHITE . count($this->players));
                    ScoreboardManager::setLine($player, 4, "§bRound: #" . TextFormat::WHITE . $this->rounds);
                    ScoreboardManager::setLine($player, 5, "§f");
            }
        }
    }
}