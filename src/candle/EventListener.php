<?php

namespace candle;

use candle\Forms\FormUtils;
use candle\Session\SessionFactory;
use candle\Tournament\TournamentTypes\BuildUHC;
use candle\Tournament\TournamentTypes\RedRover;
use candle\Tournament\TournamentTypes\Sumo;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\item\VanillaItems;

class EventListener implements Listener
{
    public function PlayerJoinEvent(PlayerJoinEvent $event): void {
        $event->getPlayer()->getInventory()->setItem(4, VanillaItems::EMERALD()->setCustomName("§bTournamenet §f(right click to use)"));
    }

    public function PlayerItemUseEvent(PlayerItemUseEvent $event): void {
        $item = $event->getItem()->getCustomName();
        $player = $event->getPlayer();
        $player->getUniqueId();
        $redrover = loader::getInstance()->redrover;
        match ($item) {
            "§cBack to lobby" => $redrover->HandlePlayerLeave($player),
            "§bTournamenet §f(right click to use)" => (new FormUtils())->CreateMainForm($player),
            default => ""
        };
    }


    public function EntityDamage(EntityDamageEvent $event): void {
        $player = $event->getEntity();
        $cause = $event->getCause();
        if ($cause === EntityDamageEvent::CAUSE_FALL || $cause === EntityDamageEvent::CAUSE_SUFFOCATION) {
            $event->cancel();
            return;
        }
        if($event instanceof EntityDamageByEntityEvent) {
            $killer = $event->getDamager();
            $RedRover = loader::getInstance()->redrover;
            $Sumo = loader::getInstance()->sumo;
            $session = SessionFactory::getSession($player);
            if($session->isInTournament("RedRover") === true) {
                if ($RedRover->state === RedRover::waiting || $RedRover->state === RedRover::countdown || loader::getInstance()->redrover->getTeam($player) === loader::getInstance()->redrover->getTeam($killer)) {
                    $event->cancel();
                }
            }
            if($session->isInTournament("Sumo") === true) {
                if($Sumo->state === Sumo::waiting || $Sumo->state === Sumo::countdown) {
                    $event->cancel();
                }
            }
            if($session->isInTournament("BuildUHC") === true) {
                if($Sumo->state === BuildUHC::waiting || $Sumo->state === BuildUHC::countdown) {
                    $event->cancel();
                }
            }
        }
    }

    public function PlayerMoveEvent(PlayerMoveEvent $event): void {
        $player = $event->getPlayer();
        $sumo = loader::getInstance()->sumo;
        if($sumo->state === Sumo::playing and $player->isSwimming() === true) {
            $sumo->HandlePlayerLeave($player);
        }
    }

    public function PlayerDeathEvent(PlayerDeathEvent $event): void {
        $player = $event->getPlayer();
        $event->setDrops([]);
        $RedRover = loader::getInstance()->redrover;
        $Sumo = loader::getInstance()->sumo;
        $session = SessionFactory::getSession($player);
        if($session->isInTournament("RedRover") === true) {
            $RedRover->HandleSpectators($player);
            $RedRover->kickTeam($player);
        }elseif($session->isInTournament("Sumo") === true) {
            $Sumo->HandleSpectators($player);
        }
    }

    public function PlayerChatEvent(PlayerChatEvent $event): void {
        $player = $event->getPlayer();
        $message = $event->getMessage();
        $RedRover = loader::getInstance()->redrover;
        $Sumo = loader::getInstance()->sumo;
        $session = SessionFactory::getSession($player);
        if($message === "leave") {
            if($session->isInTournament("RedRover") === true) {
                $RedRover->HandlePlayerLeave($player);
                $event->cancel();
            }
        }elseif ($session->isInTournament("Sumo") === true) {
            $Sumo->HandlePlayerLeave($player);
            $event->cancel();
        }
    }
}