<?php

namespace redstone\blocks;

use pocketmine\block\TNT;

class BlockTNT extends TNT implements IRedstone {
    use RedstoneTrait;
    
    public function getStrongPower(int $face) : int {
        return 0;
    }

    public function getWeakPower(int $face) : int {
        return 0;
    }

    public function isPowerSource() : bool {
        return false;
    }

    public function onRedstoneUpdate() : void {
        if ($this->isBlockPowered($this->asVector3())) {
            $this->ignite();
        }
    }
}