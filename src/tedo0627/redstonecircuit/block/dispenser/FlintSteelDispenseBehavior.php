<?php

namespace tedo0627\redstonecircuit\block\dispenser;

use pocketmine\block\Air;
use pocketmine\block\TNT;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\FlintSteel;
use pocketmine\item\Item;
use tedo0627\redstonecircuit\block\mechanism\BlockDispenser;

class FlintSteelDispenseBehavior implements DispenseItemBehavior {

    public function dispense(BlockDispenser $block, Item $item): ?Item {
        if (!$item instanceof FlintSteel) return null;

        $side = $block->getSide($block->getFacing());
        if ($side instanceof Air) {
            $item->applyDamage(1);
            $side->getPosition()->getWorld()->setBlock($side->getPosition(), VanillaBlocks::FIRE());
            return null;
        }

        if ($side instanceof TNT) {
            $item->applyDamage(1);
            $side->ignite();
        }

        return null;
    }
}