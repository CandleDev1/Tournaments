<?php

namespace candle\Tasks;

use candle\loader;
use candle\Tournament\Tournament;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class TournamentTick extends Task
{

    public function __construct(private loader $loader) {

    }

    public function onRun(): void
    {
      $this->loader->redrover->tick();
      $this->loader->sumo->tick();
      $this->loader->buildUHC->tick();
    }

}