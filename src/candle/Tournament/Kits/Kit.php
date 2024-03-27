<?php

namespace candle\Tournament\Kits;

use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\PotionType;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\color\Color;

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

//    public static function RedRoverKitBlue(Player $player)
//    {
//        $player->getArmorInventory()->setContents(
//            array_map(static fn($item) => $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)),[
//                VanillaItems::LEATHER_CAP()->setCustomColor(new Color(51, 248, 255))->setUnbreakable(),
//                VanillaItems::LEATHER_TUNIC()->setCustomColor(new Color(51, 248, 255))->setUnbreakable(),
//                VanillaItems::LEATHER_PANTS()->setCustomColor(new Color(51, 248, 255))->setUnbreakable(),
//                VanillaItems::LEATHER_BOOTS()->setCustomColor(new Color(51, 248, 255))->setUnbreakable()
//            ])
//        );
//        $sword = VanillaItems::DIAMOND_SWORD();
//        $sword->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10));
//        $contents = array_fill(0, 36, VanillaItems::SPLASH_POTION()->setType(PotionType::STRONG_HEALING()));
//        $contents[0] = $sword;
//        $contents[1] = VanillaItems::ENDER_PEARL()->setCount(16);
//        $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 90000, 0, false));
//        $player->getInventory()->setContents($contents);
//    }
//
//    public static function RedRoverKitRed(Player $player): void
//    {
//        $player->getArmorInventory()->setContents(
//            array_map(static fn($item) => $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)),[
//                VanillaItems::LEATHER_CAP()->setCustomColor(new Color(0xb0, 0x2e, 0x26))->setUnbreakable(),
//                VanillaItems::LEATHER_TUNIC()->setCustomColor(new Color(0xb0, 0x2e, 0x26))->setUnbreakable(),
//                VanillaItems::LEATHER_PANTS()->setCustomColor(new Color(0xb0, 0x2e, 0x26))->setUnbreakable(),
//                VanillaItems::LEATHER_BOOTS()->setCustomColor(new Color(0xb0, 0x2e, 0x26))->setUnbreakable()
//            ])
//        );
//        $sword = VanillaItems::DIAMOND_SWORD();
//        $sword->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10));
//        $contents = array_fill(0, 36, VanillaItems::SPLASH_POTION()->setType(PotionType::STRONG_HEALING()));
//        $contents[0] = $sword;
//        $contents[1] = VanillaItems::ENDER_PEARL()->setCount(16);
//        $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 90000, 0, false));
//        $player->getInventory()->setContents($contents);
//    }

    public static function Sumo(Player $player): void
    {
        $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 90000, 10));
        $player->getInventory()->setItem(0,VanillaItems::STICK());
    }

    public static function BackLobby(Player $player)
    {
        $player->getInventory()->setItem(8, VanillaBlocks::BED()->asItem()->setCustomName("§cBack to lobby"));
    }


}