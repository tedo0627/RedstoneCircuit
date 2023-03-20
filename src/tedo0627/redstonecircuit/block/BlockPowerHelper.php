<?php

namespace tedo0627\redstonecircuit\block;

use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;

class BlockPowerHelper {

    public static function isNormalBlock(Block $block): bool {
        if (VanillaBlocks::SLIME()->isSameType($block)) return true;
        return !$block->isTransparent() && $block->isSolid() && !self::isPowerSource($block);
    }

    public static function getStrongPower(Block $block, int $face): int {
        return $block instanceof IRedstoneComponent ? $block->getStrongPower($face) : 0;
    }

    public static function getWeakPower(Block $block, int $face): int {
        return $block instanceof IRedstoneComponent ? $block->getWeakPower($face) : 0;
    }

    public static function isPowerSource(Block $block): bool {
        return $block instanceof IRedstoneComponent ? $block->isPowerSource() : false;
    }

    public static function isPowered(Block $block, ?int $ignoreFace = null): bool {
        for ($face = 0; $face < 6; $face++) {
            if ($face === $ignoreFace) continue;
            if (self::isSidePowered($block, $face)) return true;
        }

        return false;
    }

    public static function getPower(Block $block, int $face): int {
        return self::isNormalBlock($block) ? self::getAroundStrongPower($block) : self::getWeakPower($block, $face);
    }

    public static function isSidePowered(Block $block, int $face): bool {
        return self::getPower($block->getSide($face), $face) > 0;
    }

    public static function getAroundStrongPower(Block $block): int {
        $power = 0;
        for ($face = 0; $face < 6; $face++) {
            $power = max($power, BlockPowerHelper::getStrongPower($block->getSide($face), $face));
            if ($power >= 15) return $power;
        }
        return $power;
    }
}