<?php

namespace tedo0627\redstonecircuit\block;

use pocketmine\block\Block;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\block\utils\SlabType;

class FlowablePlaceHelper {

    public static function check(Block $sideBlock, int $side): bool {
        $sideBlock = $sideBlock->getSide($side);
        if (!$sideBlock instanceof Block) return false;
        if ($sideBlock instanceof Stair) return $sideBlock->isUpsideDown();
        if ($sideBlock instanceof Slab) return $sideBlock->getSlabType() !== SlabType::BOTTOM();
        return $sideBlock->isSolid() && !$sideBlock->isTransparent();
    }
}