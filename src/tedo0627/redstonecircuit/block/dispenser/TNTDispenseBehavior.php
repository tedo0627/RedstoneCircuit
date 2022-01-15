<?php

namespace tedo0627\redstonecircuit\block\dispenser;

use pocketmine\entity\Location;
use pocketmine\entity\object\PrimedTNT;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use tedo0627\redstonecircuit\block\mechanism\BlockDispenser;

class TNTDispenseBehavior implements DispenseItemBehavior {

    public function dispense(BlockDispenser $block, Item $item): ?Item {
        $side = $block->getSide($block->getFacing());
        $item->pop();
        $pos = $side->getPosition();
        $entity = new PrimedTNT(Location::fromObject($pos->getSide(Facing::UP), $pos->getWorld()));
        $entity->spawnToAll();
        return null;
    }
}