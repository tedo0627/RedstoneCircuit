<?php

namespace tedo0627\redstonecircuit\block\dispenser;

use pocketmine\block\Grass;
use pocketmine\block\Sapling;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use tedo0627\redstonecircuit\block\mechanism\BlockDispenser;

class BoneMealDispenseBehavior implements DispenseItemBehavior {

    private DispenseItemBehavior $default;

    public function __construct() {
        $this->default = new DefaultItemDispenseBehavior();
    }

    public function dispense(BlockDispenser $block, Item $item): ?Item {
        $side = $block->getSide($block->getFacing());
        if (!$item instanceof Fertilizer) return $this->default->dispense($block, $item);

        if ($side instanceof Sapling || $side instanceof Grass) {
            $side->onInteract($item, 0, $side->getPosition());
            return null;
        }
        return null;
    }
}