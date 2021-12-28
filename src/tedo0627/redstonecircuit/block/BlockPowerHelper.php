<?php

namespace tedo0627\redstonecircuit\block;

use pocketmine\block\Block;

class BlockPowerHelper {

    public static function isNormalBlock(Block $block): bool {
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
}