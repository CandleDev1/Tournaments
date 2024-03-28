<?php

namespace candle\Session;

use pocketmine\player\Player;

class Session
{

    private Player $player;
    private $var;

    public function __construct(Player $player) {
        $this->player = $player;
    }

    public function getPlayer(): Player {
        return $this->player;
    }
    
    
    public function setInTournament(bool $var, string $tournament): ?string {
        return $this->var = $var ? $tournament : null;
    }

    public function isInTournament(string $tournament): bool {
        return $this->var === $tournament;
    }


}