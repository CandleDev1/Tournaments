<?php

namespace candle;

use pocketmine\player\Player;

class TournamentPlayer extends Player
{

    public bool $inGame = false;

    public function isInGame() : bool {
        return $this->inGame;
    }

    public function setInGame(bool $game) : void {
        $this->inGame = $game;
    }



}