<?php

namespace tedo0627\redstonecircuit\block\dispenser;

use pocketmine\block\BlockLegacyIds;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use tedo0627\redstonecircuit\block\mechanism\BlockDispenser;

class ShulkerBoxDispenseBehavior implements DispenseItemBehavior {

    public function dispense(BlockDispenser $block, Item $item): ?Item {
        $face = $block->getFacing();
        $side = $block->getSide($face);
        if ($side->getId() !== BlockLegacyIds::AIR) return null;

        $targetFace = null;
        $targetBlock = null;
        for ($i = 0; $i < 6; $i++) {
            if (Facing::opposite($face) === $i) continue;

            $target = $side->getSide($i);
            if (!$target->isSolid()) continue;

            $targetFace = $i;
            $targetBlock = $target;
        }

        if ($targetFace === null) {
            $targetFace = Facing::opposite($face);
            $targetBlock = $block;
        }

        $pop = $item->pop();
        $side->getPosition()->getWorld()->useItemOn($targetBlock->getPosition(), $pop, Facing::opposite($targetFace));
        return null;
    }
}