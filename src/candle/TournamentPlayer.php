<?php

namespace candle;

use pocketmine\player\Player;

class TournamentPlayer extends Player
{

    public ?string $game = null;

    public function isInGame(string $tournament) : bool {
        return $this->game === $tournament;
    }

    public function setInGame(bool $game, string $tournament) : void {
        $this->game = $game ? $tournament : null;
    }



}
