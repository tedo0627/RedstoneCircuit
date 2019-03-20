<?php

namespace redstone\blocks;

use pocketmine\block\FenceGate;

use pocketmine\level\sound\DoorSound;

class BlockFenceGate extends FenceGate implements IRedstone {
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
            if (($this->getDamage() & 0x04) != 0x04) {
                $this->setDamage($this->getDamage() ^ 0x04);
                $this->level->addSound(new DoorSound($this));
            }
        } else {
            if (($this->getDamage() & 0x04) == 0x04) {
                $this->setDamage($this->getDamage() ^ 0x04);
                $this->level->addSound(new DoorSound($this));
            }
        }

        $this->level->setBlock($this, $this, true);
    }
}