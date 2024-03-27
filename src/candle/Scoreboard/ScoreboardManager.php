<?php

namespace candle\Scoreboard;

use pocketmine\event\Listener;
use pocketmine\network\mcpe\protocol\{RemoveObjectivePacket, SetDisplayObjectivePacket, SetScorePacket};
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;

class ScoreboardManager implements Listener
{

    /** @var string[] */
    public static $scoreboards = [];

    public static function new(Player $player, string $objectiveName, string $displayName, int $sortOrder = 0): void
    {
        if (isset(self::$scoreboards[$player->getName()])) {
            self::remove($player);
        }
        $pk = new SetDisplayObjectivePacket();
        $pk->displaySlot = "sidebar";
        $pk->objectiveName = $objectiveName;
        $pk->displayName = $displayName;
        $pk->criteriaName = "dummy";
        $pk->sortOrder = $sortOrder;
        $player->getNetworkSession()->sendDataPacket($pk);
        self::$scoreboards[$player->getName()] = $objectiveName;
    }

    public static function remove(Player $player): void
    {
        if (self::getObjectiveName($player) !== null) {
            $objectiveName = self::getObjectiveName($player);
            $pk = new RemoveObjectivePacket();
            $pk->objectiveName = $objectiveName;
            $player->getNetworkSession()->sendDataPacket($pk);
            unset(self::$scoreboards[$player->getName()]);
        }
    }

    public static function getObjectiveName(Player $player): ?string
    {
        return isset(self::$scoreboards[$player->getName()]) ? self::$scoreboards[$player->getName()] : null;
    }

    public static function setLine(Player $player, int $score, string $message): void
    {
        if (!isset(self::$scoreboards[$player->getName()])) {
            return;
        }
        if ($score > 15 || $score < 1) {
            error_log("Score must be between the value of 1-15. $score out of range");
            return;
        }
        $objectiveName = self::getObjectiveName($player);
        $entry = new ScorePacketEntry();
        $entry->objectiveName = $objectiveName;
        $entry->type = $entry::TYPE_FAKE_PLAYER;
        $entry->customName = $message;
        $entry->score = $score;
        $entry->scoreboardId = $score;
        $pk = new SetScorePacket();
        $pk->type = $pk::TYPE_CHANGE;
        $pk->entries[] = $entry;
        $player->getNetworkSession()->sendDataPacket($pk);
    }
}