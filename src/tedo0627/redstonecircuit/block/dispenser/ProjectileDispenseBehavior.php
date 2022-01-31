<?php

namespace tedo0627\redstonecircuit\block\dispenser;

use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use tedo0627\redstonecircuit\block\mechanism\BlockDispenser;

abstract class ProjectileDispenseBehavior implements DispenseItemBehavior {

    public function dispense(BlockDispenser $block, Item $item): ?Item {
        $pos = $block->getPosition();
        $world = $pos->getWorld();
        $facePos = Vector3::zero()->getSide($block->getFacing());
        $pos = $pos->add(0.5, 0.5, 0.5)->addVector($facePos->multiply(0.6));
        $entity = $this->getEntity(Location::fromObject($pos, $world), $item);
        $facePos = $facePos->add(0, 0.1, 0)->add(
            mt_rand(-100, 100) / 100 * 0.0075 * 1.5,
            mt_rand(-100, 100) / 100 * 0.0075 * 1.5,
            mt_rand(-100, 100) / 100 * 0.0075 * 1.5
        );
        $entity->addMotion($facePos->getX(), $facePos->getY(), $facePos->getZ());
        $entity->spawnToAll();
        $item->pop();
        return null;
    }

    public abstract function getEntity(Location $location, Item $item): Entity;
}