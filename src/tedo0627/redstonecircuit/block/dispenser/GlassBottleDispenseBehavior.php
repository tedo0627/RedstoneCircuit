<?php

namespace tedo0627\redstonecircuit\block\dispenser;

use pocketmine\block\Water;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use tedo0627\redstonecircuit\block\mechanism\BlockDispenser;

class GlassBottleDispenseBehavior implements DispenseItemBehavior {

    private DispenseItemBehavior $default;

    public function __construct() {
        $this->default = new DefaultItemDispenseBehavior();
    }

    public function dispense(BlockDispenser $block, Item $item): ?Item {
        $side = $block->getSide($block->getFacing());
        if (!$side instanceof Water) return $this->default->dispense($block, $item);

        $item->pop();
        return VanillaItems::WATER_POTION();
    }
}