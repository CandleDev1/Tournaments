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
use pocketmine\world\sound\AnvilBreakSound;
use pocketmine\math\Vector3;
use pocketmine\world\sound\AnvilUseSound;
use pocketmine\world\sound\XpCollectSound;

#[AllowDynamicProperties] class RedRover extends Tournament {

    const int idle = 0;
    const int waiting = 1;
    const int countdown = 2;
    const int playing = 3;

    public bool $fight = false;
    public int $state = RedRover::idle;

    public int $countdown = 10;

    /** @var Player[] */
    public array $players = [];


    public function setUpArena(Player $player): void {
        $this->loadArena($player, "RedRover");
        $this->AnnounceTournament("RedRover");
        $this->state = RedRover::waiting;
    }

    public function StartTournament(Player $player, int $minPlayers,int $maxplayers, int $rewardID): void
    {
        if($minPlayers > $maxplayers) {
            $player->sendMessage(loader::PREFIX . "Minimum players cannot be higher than maximum players!");
            return;
        }
        if($this->state === RedRover::playing || $this->state === RedRover::countdown || $this->state === RedRover::waiting) {
            $player->sendMessage(loader::PREFIX . "Theres already a Tournament running");
            return;
        }

        $this->minPlayers = $minPlayers;
        $this->maxPlayers = $maxplayers;

        $this->state = RedRover::waiting;

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
        if($this->state === RedRover::playing) {
            $player->sendMessage(loader::PREFIX . "The RedRover event has already begun.");
            return;
        }

        $this->players[] = $player;
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();

        $this->TeleportArena($player, "RedRover");
        $this->setTeam($player);
        $this->AnnouncePlayerJoined($player, "RedRover");
        $this->setKit($player, "backlobby");
        $player->setInGame(true, "RedRover");
    }

    public function HandlePlayerLeave(Player $player): void {
        $this->TeleportSpawn($player, "RedRover");
        $this->AnnouncePlayerLeft($player, "RedRover");
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getInventory()->setItem(4, VanillaItems::EMERALD()->setCustomName("§bTournamenet §f(right click to use)"));
        ScoreboardManager::remove($player);
        $player->setInGame(false, "RedRover");
        $player->setGamemode(GameMode::SURVIVAL);
        unset($this->players[array_search($player, $this->players, true)]);
    }

    public function HandleSpectators(Player $player): void {
        $this->TeleportArena($player, "RedRover");
        $player->setGamemode(GameMode::SPECTATOR);
        $player->setInGame(true, "RedRover");
    }


    public function Fighting(): void {
        if($this->state !== RedRover::playing) {
            return;
        }

        $this->fight = true;
        foreach ($this->players as $player) {
            $player->getInventory()->clearAll();
            $this->setKit($player, "RedRover");
            $this->AnnounceTournamentStarted("RedRover");
            if(isset($this->blueTeam[$player->getName()])) {
                $player->teleport(new Position(loader::getInstance()->getConfig()->getNested("TournamentWorlds.BlueTeamSpawn.x"), loader::getInstance()->getConfig()->getNested("TournamentWorlds.BlueTeamSpawn.y"), loader::getInstance()->getConfig()->getNested("TournamentWorlds.BlueTeamSpawn.z"), Server::getInstance()->getWorldManager()->getWorldByName(loader::getInstance()->getConfig()->getNested("TournamentWorlds.BlueTeamSpawn.world"))));
            } elseif(isset($this->redTeam[$player->getName()])) {
                $player->teleport(new Position(loader::getInstance()->getConfig()->getNested("TournamentWorlds.RedTeamSpawn.x"), loader::getInstance()->getConfig()->getNested("TournamentWorlds.RedTeamSpawn.y"), loader::getInstance()->getConfig()->getNested("TournamentWorlds.RedTeamSpawn.z"), Server::getInstance()->getWorldManager()->getWorldByName(loader::getInstance()->getConfig()->getNested("TournamentWorlds.RedTeamSpawn.world"))));            }
        }
    }


    public function tick(): void
    {
        $this->sendScore();
        switch ($this->state) {
            case RedRover::waiting:
                if (count($this->players) > 1 and count($this->redTeam) >= 1 and count($this->blueTeam) >= 1) {
                    $this->state = RedRover::countdown;
                }
                break;
            case RedRover::countdown;
                if (count($this->players) <= 1 or count($this->redTeam) < 1 or count($this->blueTeam) < 1) {
                    $this->state = RedRover::waiting;
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
                $this->state = RedRover::playing;
                break;
            case RedRover::playing:
                if(!$this->fight) {
                    $this->Fighting();
                }

                if (count($this->redTeam) > count($this->blueTeam)) {
                    Server::getInstance()->broadcastMessage(loader::PREFIX . "Team red has won the RedRover Tournament");
                    $this->StopTournament();
                }elseif(count($this->blueTeam) > count($this->redTeam)) {
                    Server::getInstance()->broadcastMessage(loader::PREFIX . "Team blue has won the RedRover Tournament");
                    $this->StopTournament();
                }
        }
    }


    public function sendScore(): void
    {
        $states = [RedRover::idle => "Idle", RedRover::waiting => "Waiting", RedRover::countdown => "Countdown", RedRover::playing => "Running"];
        foreach ($this->players as $player) {
            switch ($this->state) {
                case RedRover::waiting:
                    ScoreboardManager::new($player, "1", "§c§bTournaments");
                    ScoreboardManager::setLine($player, 1, '');
                    ScoreboardManager::setLine($player, 2, "§bTournament: " . TextFormat::WHITE . "RedRover");
                    ScoreboardManager::setLine($player, 3, "§bPlayers: " . TextFormat::WHITE . count($this->players));
                    ScoreboardManager::setLine($player, 4, "§bStatus: " . TextFormat::WHITE . $states[$this->state]);
                    ScoreboardManager::setLine($player, 5, "§f");
                    break;
                case RedRover::countdown:
                    ScoreboardManager::new($player, "2","§c§bTournaments");
                    ScoreboardManager::setLine($player, 1, '');
                    ScoreboardManager::setLine($player, 2, "§bTournament: " . TextFormat::WHITE . "(RedRover)");
                    ScoreboardManager::setLine($player, 3, "§bPlayers: " . TextFormat::WHITE . count($this->players));
                    ScoreboardManager::setLine($player, 4, "§bStarting in: " . TextFormat::WHITE . gmdate("i:s", $this->countdown));
                    ScoreboardManager::setLine($player, 5, "§f");
                    break;
                case RedRover::playing:
                    ScoreboardManager::new($player, "3", "§c§bTournaments");
                    ScoreboardManager::setLine($player, 1, TextFormat::GRAY . '');
                    ScoreboardManager::setLine($player, 2, "§bEvent " . TextFormat::WHITE . "(RedRover)");
                    ScoreboardManager::setLine($player, 3, "§bPlayers: " . TextFormat::WHITE . count($this->players));
                    ScoreboardManager::setLine($player, 4, "§bTeam: " . TextFormat::WHITE . $this->getTeam($player));
                    ScoreboardManager::setLine($player, 5, "§f");
            }
        }
    }



    public function StopTournament(): void {
        foreach ($this->players as $player) {
            $this->HandlePlayerLeave($player);
        }
        $this->players = [];
        $this->state = RedRover::idle;
        $this->countdown = 10;
        $this->fight = false;
        $this->redTeam = [];
        $this->blueTeam = [];
    }


}
