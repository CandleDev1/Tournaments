<?php

namespace candle\Tournament\TournamentTypes;

use AllowDynamicProperties;
use candle\loader;
use candle\Scoreboard\ScoreboardManager;
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


    /**
     * @var Player|null
     */
    public $opponent_1 = null;

    /**
     * @var Player|null
     */
    public $opponent_2 = null;

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

    public function HandlePlayerJoin(TournamentPlayer $player): void {
        if($this->state === Sumo::playing) {
            $player->sendMessage(loader::PREFIX . "The RedRover event has already begun.");
            return;
        }
        $this->players[] = $player;
        $player->getInventory()->clearAll();
        //Test
        $player->getArmorInventory()->clearAll();

        $this->TeleportArena($player, "Sumo");
        $this->AnnouncePlayerJoined($player, "Sumo");
        $this->setKit($player, "backlobby");
        $player->setInGame(true);
    }

    public function HandlePlayerLeave(TournamentPlayer $player): void {
        $this->TeleportSpawn($player, "Sumo");
        $this->AnnouncePlayerLeft($player, "Sumo");
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getInventory()->setItem(4, VanillaItems::EMERALD()->setCustomName("§bTournamenet §f(right click to use)"));
        ScoreboardManager::remove($player);
        $player->setInGame(false);
        $player->setGamemode(GameMode::SURVIVAL);
        unset($this->players[array_search($player, $this->players, true)]);
    }

    public function HandleSpectators(TournamentPlayer $player): void {
        $this->TeleportArena($player, "Sumo");
        $player->setGamemode(GameMode::SPECTATOR);
        $player->setInGame(true);
    }




    public function Fighting(): void {
        if($this->state !== Sumo::playing) {
            return;
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
                    }
                }
                if($this->fight) {
                    if($this->fightcooldown !== null and $this->fightcooldown > 0) {
                        $this->opponent_1->sendTitle(TextFormat::GOLD . $this->fightcooldown);
                        $this->opponent_2->sendTitle(TextFormat::GOLD . $this->fightcooldown);
                        $this->fightcooldown--;
                    } elseif ($this->fightcooldown !== null and $this->fightcooldown <= 0) {
                        $this->opponent_1->setNoClientPredictions(false);
                        $this->opponent_1->setNoClientPredictions(false);
                        $this->fightcooldown = null;
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
                    ScoreboardManager::setLine($player, 2, "§bEvent " . TextFormat::WHITE . "(RedRover)");
                    ScoreboardManager::setLine($player, 3, "§bPlayers: " . TextFormat::WHITE . count($this->players));
                    ScoreboardManager::setLine($player, 4, "§bRound: #" . TextFormat::WHITE . $this->rounds);
                    ScoreboardManager::setLine($player, 5, "§f");
            }
        }
    }
}