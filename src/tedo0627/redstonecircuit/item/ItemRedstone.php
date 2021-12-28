<?php

namespace tedo0627\redstonecircuit\item;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\item\Item;

class ItemRedstone extends Item{

    public function getBlock(?int $clickedFace = null) : Block{
        return BlockFactory::getInstance()->get(BlockLegacyIds::REDSTONE_WIRE, 0);
    }
}