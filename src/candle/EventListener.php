<?php

namespace candle;

use candle\Forms\FormUtils;
use candle\Tournament\TournamentTypes\RedRover;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\VanillaItems;

class EventListener implements Listener
{

    public function PlayerCreationEvent(PlayerCreationEvent $event) {
        $event->setPlayerClass(TournamentPlayer::class);
    }

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
        }
        if($event instanceof EntityDamageByEntityEvent) {
            $killer = $event->getDamager();
            $RedRover = loader::getInstance()->redrover;
            if ($player instanceof TournamentPlayer and $killer instanceof TournamentPlayer) {
                if ($RedRover->state === RedRover::waiting || $RedRover->state === RedRover::countdown || loader::getInstance()->redrover->getTeam($player) === loader::getInstance()->redrover->getTeam($killer)) {
                    $event->cancel();
                    return;
                }
            }
        }
    }

    public function PlayerDeathEvent(PlayerDeathEvent $event): void {
        $player = $event->getPlayer();
        $event->setDrops([]);
        $RedRover = loader::getInstance()->redrover;
        $Sumo = loader::getInstance()->sumo;

        if($player instanceof TournamentPlayer){
            if($player->isInGame("RedRover") === true) {
                $RedRover->HandleSpectators($player);
                $RedRover->kickTeam($player);
            }elseif($player->isInGame("Sumo")) {
                $Sumo->HandleSpectators($player);
            }
        }
    }

    public function PlayerChatEvent(PlayerChatEvent $event): void {
        $player = $event->getPlayer();
        $message = $event->getMessage();
        if($message === "leave") {
            if($player instanceof TournamentPlayer) {
                if($player->isInGame("RedRover") === true) {
                    loader::getInstance()->redrover->HandlePlayerLeave($player);
                    $event->cancel();
                }elseif($player->isInGame("RedRover") === true) {
                    loader::getInstance()->sumo->HandlePlayerLeave($player);
                    $event->cancel();
                }
            }
        }
    }
}