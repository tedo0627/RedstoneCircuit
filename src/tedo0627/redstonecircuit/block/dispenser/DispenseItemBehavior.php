<?php

namespace tedo0627\redstonecircuit\block\dispenser;

use pocketmine\item\Item;
use tedo0627\redstonecircuit\block\mechanism\BlockDispenser;

interface DispenseItemBehavior {

    public function dispense(BlockDispenser $block, Item $item): ?Item;
}