<?php

namespace tedo0627\redstonecircuit\block;

use pocketmine\block\Block;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\block\utils\SlabType;
use pocketmine\math\Facing;

class FlowablePlaceHelper {

    public static function check(Block $block, int $side): bool {
        $sideBlock = $block->getSide($side);
        if ($sideBlock instanceof Stair) return $sideBlock->isUpsideDown();
        if ($sideBlock instanceof Slab) return $sideBlock->getSlabType() !== SlabType::BOTTOM();
        return $sideBlock->isSolid() && !$sideBlock->isTransparent();
    }

    public static function checkSurface(Block $block, int $side): bool {
        $sideBlock = $block->getSide($side);
        if ($sideBlock instanceof Stair) {
            if ($sideBlock->isUpsideDown() && $side === Facing::DOWN) return true;
            if (!$sideBlock->isUpsideDown() && $side === Facing::UP) return true;

            return $side === Facing::opposite($sideBlock->getFacing());
        }

        if ($sideBlock instanceof Slab) {
            return match ($sideBlock->getSlabType()) {
                SlabType::BOTTOM() => $side == Facing::UP,
                SlabType::TOP() => $side == Facing::DOWN,
                SlabType::DOUBLE() => true
            };
        }
        return $sideBlock->isSolid() && !$sideBlock->isTransparent();
    }
}