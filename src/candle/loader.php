<?php

namespace candle;

use AllowDynamicProperties;
use candle\Session\SessionListener;
use candle\Tasks\TournamentTick;
use candle\Tournament\TournamentSystem;
use candle\Tournament\TournamentTypes\RedRover;
use candle\Tournament\TournamentTypes\Sumo;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

#[AllowDynamicProperties] class loader extends PluginBase
{

    use SingletonTrait;

    const PREFIX = "§cTournament §r";
    public RedRover $redrover;
    public Sumo $sumo;

    public function onLoad(): void
    {
        self::$instance = $this;
    }

    public static function getInstance(): self
    {
        return self::$instance;
    }

    public function onEnable(): void
    {
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new SessionListener(), $this);
        
        $this->redrover = new RedRover();
        $this->sumo = new Sumo();
        $this->Tournament = new TournamentSystem();
        $this->getScheduler()->scheduleRepeatingTask(new TournamentTick($this), 20);
        $this->getServer()->getWorldManager()->loadWorld("lunar_spawn");

    }

}