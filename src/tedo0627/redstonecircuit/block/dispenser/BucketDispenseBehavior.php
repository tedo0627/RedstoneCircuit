<?php

namespace tedo0627\redstonecircuit\block\dispenser;

use pocketmine\block\Lava;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Water;
use pocketmine\item\Item;
use pocketmine\item\LiquidBucket;
use pocketmine\item\VanillaItems;
use tedo0627\redstonecircuit\block\mechanism\BlockDispenser;

class BucketDispenseBehavior implements DispenseItemBehavior {

    private DispenseItemBehavior $default;

    public function __construct() {
        $this->default = new DefaultItemDispenseBehavior();
    }

    public function dispense(BlockDispenser $block, Item $item): ?Item {
        $side = $block->getSide($block->getFacing());
        if ($item instanceof LiquidBucket) {
            if (!$side->canBeReplaced()) return $this->default->dispense($block, $item);

            $side->getPosition()->getWorld()->setBlock($side->getPosition(), $item->getLiquid());
            $item->pop();
            return VanillaItems::BUCKET();
        }

        $result = match (true) {
            $side instanceof Water => VanillaItems::WATER_BUCKET(),
            $side instanceof Lava => VanillaItems::LAVA_BUCKET(),
            default => null
        };
        if ($result === null) return $this->default->dispense($block, $item);

        $side->getPosition()->getWorld()->setBlock($side->getPosition(), VanillaBlocks::AIR());
        $item->pop();
        return $result;
    }
}