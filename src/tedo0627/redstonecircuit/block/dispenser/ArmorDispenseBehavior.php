<?php

namespace tedo0627\redstonecircuit\block\dispenser;

use pocketmine\entity\Human;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use tedo0627\redstonecircuit\block\mechanism\BlockDispenser;

class ArmorDispenseBehavior implements DispenseItemBehavior {

    private int $slot;
    private DispenseItemBehavior $default;

    public function __construct(int $slot) {
        $this->slot = $slot;
        $this->default = new DefaultItemDispenseBehavior();
    }

    public function dispense(BlockDispenser $block, Item $item): ?Item {
        $side = $block->getSide($block->getFacing());
        $pos = $side->getPosition();
        $entities = $pos->getWorld()->getNearbyEntities(new AxisAlignedBB($pos->x, $pos->y, $pos->z, $pos->x + 1, $pos->y + 1, $pos->z + 1));
        foreach ($entities as $entity) {
            if (!$entity instanceof Human) continue;

            $entity->getArmorInventory()->setItem($this->slot, $item->pop());
            return null;
        }
        return $this->default->dispense($block, $item);
    }
}