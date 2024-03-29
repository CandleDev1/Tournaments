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

    public static function BuildUHC(Player $player): void {
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
        $bow = VanillaItems::BOW();
        $bow->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 1));
        $arrow = VanillaItems::ARROW()->setCount(64);
        $gaps = VanillaItems::GOLDEN_APPLE()->setCount(64);
        $block1 = VanillaBlocks::OAK_LOG()->asItem()->setCount(64);
        $block2 = VanillaBlocks::STONE()->asItem()->setCount(64);
        $steak = VanillaItems::STEAK()->setCount(64);
        $water = VanillaItems::WATER_BUCKET()->setCount(2);
        $lava = VanillaItems::LAVA_BUCKET()->setCount(2);
        $pickaxe = VanillaItems::DIAMOND_PICKAXE();
        $axe = VanillaItems::DIAMOND_AXE();
        $player->getInventory()->setContents([
            0 => $sword,
            1 => $bow,
            2 => $gaps,
            3 => $block1,
            4 => $block2,
            5 => $water,
            6 => $lava,
            7 => $axe,
            8 => $steak,
            17 => $arrow,
            34 => $pickaxe
        ]);

    }

    public static function BackLobby(Player $player)
    {
        $player->getInventory()->setItem(8, VanillaBlocks::BED()->asItem()->setCustomName("§cBack to lobby"));
    }


}