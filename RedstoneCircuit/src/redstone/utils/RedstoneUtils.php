<?php

namespace redstone\utils;

use pocketmine\block\Block;

use pocketmine\level\Level;

use pocketmine\math\Facing;
use pocketmine\math\Vector3;


use redstone\blocks\IRedstone;

class RedstoneUtils {

    public static function isNormalBlock(Block $block) : bool {
        return !$block->isTransparent() && $block->isSolid() && !RedstoneUtils::isPowerSource($block);
    }

    public static function getStrongPower(Block $block, int $face) : int {
        if ($block instanceof IRedstone){
            return $block->getStrongPower($face);
        } else {
            return 0;
        }
    }

    public static function getWeakPower(Block $block, int $face) : int {
        if ($block instanceof IRedstone) {
            return $block->getWeakPower($face);
        } else {
            return 0;
        }
    }

    public static function isPowerSource(Block $block) : bool {
        if ($block instanceof IRedstone) {
            return $block->isPowerSource();
        }
        return false;
    }
}