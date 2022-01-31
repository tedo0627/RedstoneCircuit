<?php

namespace tedo0627\redstonecircuit\block\dispenser;

use pocketmine\item\Item;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use tedo0627\redstonecircuit\block\mechanism\BlockDispenser;

class DefaultItemDispenseBehavior implements DispenseItemBehavior {

    public function dispense(BlockDispenser $block, Item $item): ?Item {
        $pos = $block->getPosition();
        $world = $pos->getWorld();
        $face = $block->getFacing();
        $drop = clone $item;
        $drop->setCount(1);
        $facePos = Vector3::zero()->getSide($face)->multiply(0.6);
        $pos = $pos->add(0.5, 0.5, 0.5)->addVector($facePos);
        $v = mt_rand(0, 100) / 1000 + 0.2;
        $motion = new Vector3(
            mt_rand(-100, 100) / 100 * 0.0075 * 6 + (Facing::axis($face) == Axis::X ? 1.0 : 0.0) * $v * (Facing::isPositive($face) ? 1.0 : -1.0),
            mt_rand(-100, 100) / 100 * 0.0075 * 6 + 0.2,
            mt_rand(-100, 100) / 100 * 0.0075 * 6 + (Facing::axis($face) == Axis::Z ? 1.0 : 0.0) * $v * (Facing::isPositive($face) ? 1.0 : -1.0),
        );
        $world->dropItem($pos, $drop, $motion);
        $item->pop();
        return null;
    }
}