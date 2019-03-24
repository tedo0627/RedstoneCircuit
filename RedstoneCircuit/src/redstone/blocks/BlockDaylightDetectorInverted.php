<?php

namespace redstone\blocks;

use pocketmine\Player;

use pocketmine\item\Item;

class BlockDaylightDetectorInverted extends BlockDaylightDetector {
    
    protected $id = self::DAYLIGHT_DETECTOR_INVERTED;
    
    public function onActivate(Item $item, Player $player = null) : bool {
        $this->getLevel()->setBlock($this, new BlockDaylightDetector(), true, true);
        $this->getLevel()->getBlock($this)->updatePower();
        return true;
    }
}