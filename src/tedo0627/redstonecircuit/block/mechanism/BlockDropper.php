<?php

namespace tedo0627\redstonecircuit\block\mechanism;

use pocketmine\item\Item;

class BlockDropper extends BlockDispenser {

    public function dispense(Item $item): ?Item {
        return BlockDispenser::$default->dispense($this, $item);
    }
}