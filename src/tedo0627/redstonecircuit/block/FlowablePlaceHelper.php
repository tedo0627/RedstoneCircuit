<?php

namespace tedo0627\redstonecircuit\block;

use pocketmine\block\Block;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\block\utils\SlabType;

class FlowablePlaceHelper {

    public static function check(Block $block, int $side): bool {
        $sideBlock = $block->getSide($side);
        if (!$sideBlock instanceof Block) return false;
        if ($sideBlock instanceof Stair) return $sideBlock->isUpsideDown();
        if ($sideBlock instanceof Slab) return $sideBlock->getSlabType() !== SlabType::BOTTOM();
        return $sideBlock->isSolid() && !$sideBlock->isTransparent();
    }
}