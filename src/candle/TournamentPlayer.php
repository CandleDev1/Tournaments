<?php

namespace candle;

use pocketmine\player\Player;

class TournamentPlayer extends Player
{

    public bool $inGame = false;

    public function isInGame(string $tournament) : bool {
        return $this->inGame;
    }

    public function setInGame(bool $game, string $tournament) : void {
        $this->inGame = $game;
    }



}