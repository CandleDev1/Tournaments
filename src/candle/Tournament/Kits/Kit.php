<?php

namespace candle\Tournament\Kits;

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\PotionType;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

class Kit
{

    public static function RedRoverKit(Player $player)
    {
        $player->getArmorInventory()->setContents(
            array_map(static fn($item) => $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)),[
                VanillaItems::DIAMOND_HELMET()->setUnbreakable(),
                VanillaItems::DIAMOND_CHESTPLATE()->setUnbreakable(),
                VanillaItems::DIAMOND_LEGGINGS()->setUnbreakable(),
                VanillaItems::DIAMOND_BOOTS()->setUnbreakable()
            ])
        );
        $sword = VanillaItems::DIAMOND_SWORD();
        $sword->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10));
        $contents = array_fill(0, 36, VanillaItems::SPLASH_POTION()->setType(PotionType::STRONG_HEALING()));
        $contents[0] = $sword;
        $contents[1] = VanillaItems::ENDER_PEARL()->setCount(16);
        $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 90000, 0, false));
        $player->getInventory()->setContents($contents);
    }

    public static function Sumo(Player $player): void
    {
        $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 90000, 5, false));
        $player->getInventory()->setItem(0,VanillaItems::STICK());
    }

    public static function BackLobby(Player $player)
    {
        $player->getInventory()->setItem(8, VanillaBlocks::BED()->asItem()->setCustomName("§cBack to lobby"));
    }


}