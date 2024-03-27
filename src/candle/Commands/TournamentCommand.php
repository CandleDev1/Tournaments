<?php

namespace candle\Commands;

use candle\Forms\FormUtils;
use candle\loader;
use candle\Tournament\TournamentTypes\RedRover;
use candle\TournamentPlayer;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class TournamentCommand extends Command
{

    public function __construct(){
        parent::__construct("tournament");
        $this->setPermission(loader::getInstance()->getConfig()->get("HostTournaments"));
        $this->setUsage("/tournament create/join/leave");
    }

   public function execute(CommandSender $sender, string $commandLabel, array $args)
   {
       if($sender instanceof TournamentPlayer) {
           if(!isset($args[0])) {
               $sender->sendMessage($this->getUsage());
               return;
           }

           $redrover = loader::getInstance()->redrover;

           if($args[0] === "join") {
               if($redrover->state === RedRover::idle) {
                   $sender->sendMessage(loader::PREFIX . "Theres currently no event running");
                   return;
               }elseif($redrover->state === RedRover::playing || $redrover->state === RedRover::countdown) {
                   $sender->sendMessage(loader::PREFIX . "The event has begun use Tournament item in ur hotbar to spectate!");
                   return;
               } else {
                   $redrover->HandlePlayerJoin($sender);
                   return;
               }
           }
           match($args[0]) {
               "leave" => $redrover->HandlePlayerLeave($sender),
               "create" => (new FormUtils())->CreateMainForm($sender),
               "state" => $sender->sendMessage($redrover->state),
               default => $sender->sendMessage($this->getUsage())
           };


       }

   }

}