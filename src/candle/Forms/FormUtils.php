<?php

namespace candle\Forms;

use candle\loader;
use candle\Tournament\TournamentTypes\RedRover;
use candle\Tournament\TournamentTypes\Sumo;
use EasyUI\element\Button;
use EasyUI\element\Dropdown;
use EasyUI\element\Input;
use EasyUI\element\Option;
use EasyUI\utils\FormResponse;
use EasyUI\variant\CustomForm;
use EasyUI\variant\SimpleForm;
use pocketmine\player\Player;
use pocketmine\Server;

class FormUtils
{

    public function CreateMainForm(Player $player): void {
        $form = new SimpleForm("Tournamenets");

        $create = new Button("Create a tournament");
        $join = new Button("Join a tournament");
        $spectate = new Button("Spectate a tournament");

        $create->setSubmitListener(function (Player $player) {
            if($player->hasPermission("pocketmine.group.operator")) {
                $this->CreateTournamentForm($player, loader::getInstance()->redrover, loader::getInstance()->sumo);
            }
        });

        $join->setSubmitListener(function (Player $player) {
            $this->JoinTournamentForm($player, loader::getInstance()->redrover, loader::getInstance()->sumo);
        });

        $spectate->setSubmitListener(function (Player $player) {
            $this->SpectateTournamentForm($player, loader::getInstance()->redrover, loader::getInstance()->sumo);
        });

        $form->addButton($create);
        $form->addButton($join);
        $form->addButton($spectate);
        $player->sendForm($form);
    }

    public function CreateTournamentForm(Player $player, RedRover $redRover, Sumo $sumo): void {
        $form = new CustomForm("Create a tournament");

        $type = new Dropdown("Select a type Tournament");
        $type->addOption(new Option(1, "RedRover"));
        $type->addOption(new Option(2, "Sumo"));
        $type->addOption(new Option(3, "Build UHC"));

        $minplayers = new Input("Minum players for tournament to start");
        $maxplayers = new Input("Maximum players that can join the tournament");

        $reward = new Dropdown("Winners Reward");
        $reward->addOption(new Option(1, "Coins"));
        $reward->addOption(new Option(2, "Custom rank"));


        $form->setSubmitListener(function (Player $player, FormResponse $response) use ($redRover, $sumo){
            $type = $response->getDropdownSubmittedOptionId(1);
            $minplayers = $response->getInputSubmittedText(3);
            $maxplayers = $response->getInputSubmittedText(4);
            $reward = $response->getDropdownSubmittedOptionId(5);


            if($minplayers === null) {
                $player->sendMessage(loader::PREFIX . "Enter a minum player count to start the Event!");
                return;
            } elseif ($maxplayers === null) {
                $player->sendMessage(loader::PREFIX . "Enter an max player count!");
                return;
            }
            if($type == 1) {
                $redRover->StartTournament($player, $minplayers, $maxplayers, $reward);
            }elseif ($type == 2) {
                $sumo->StartTournament($player, $minplayers,$maxplayers, $reward);
            }

        });
        $form->addElement(1, $type);
        $form->addElement(3,$minplayers);
        $form->addElement(4,$maxplayers);
        $form->addElement(5, $reward);

        $player->sendForm($form);

    }

    public function JoinTournamentForm(Player $player, RedRover $redRover, Sumo $sumo) {
        $form = new SimpleForm("Join an active Tournament");
        $startedTournament = $redRover->state === RedRover::waiting;
        $startedTournamentSumo = $sumo->state === Sumo::waiting;
        if ($startedTournament) {
            $playerCount = count(Server::getInstance()->getWorldManager()->getWorldByName("lunar_spawn")->getPlayers());
            $button = new Button("RedRover\nPlayers: " . $playerCount);
            $button->setSubmitListener(function (Player $player) {
                $redrover = loader::getInstance()->redrover;
                $redrover->HandlePlayerJoin($player);
            });
            $form->addButton($button);
        }
        if($startedTournamentSumo) {
            $playerCount = count(Server::getInstance()->getWorldManager()->getWorldByName("mine")->getPlayers());
            $buttonsumo = new Button("Sumo\nPlayers: " . $playerCount);
            $buttonsumo->setSubmitListener(function (Player $player) {
                $sumo = loader::getInstance()->sumo;
                $sumo->HandlePlayerJoin($player);
            });
            $form->addButton($buttonsumo);
        }
        $player->sendForm($form);
    }

    public function SpectateTournamentForm(Player $player, RedRover $redRover, Sumo $sumo): void {
        $form = new SimpleForm("Spectate a active Tournament");
        $startedTournament = $redRover->state === RedRover::playing || $redRover->state === RedRover::countdown;
        $startedTournamentSumo = $sumo->state === Sumo::playing || $sumo->state === Sumo::countdown;
        if ($startedTournament) {
            $playerCount = count(Server::getInstance()->getWorldManager()->getWorldByName("lunar_spawn")->getPlayers());
            $button = new Button("RedRover\nPlayers: " . $playerCount);
            $button->setSubmitListener(function (Player $player) {
                $redrover = loader::getInstance()->redrover;
                $redrover->HandleSpectators($player);
            });
            $form->addButton($button);
        }
        if($startedTournamentSumo) {
            $playerCount = count(Server::getInstance()->getWorldManager()->getWorldByName("mine")->getPlayers());
            $buttonsumo = new Button("Sumo\nPlayers: " . $playerCount);
            $buttonsumo->setSubmitListener(function (Player $player) {
                $sumo = loader::getInstance()->sumo;
                $sumo->HandleSpectators($player);
            });
            $form->addButton($buttonsumo);
        }

        $player->sendForm($form);
    }





}