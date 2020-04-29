<?php

namespace redstone\blocks;

use pocketmine\block\WoodenDoor;

use pocketmine\level\sound\DoorSound;

use redstone\utils\Facing;

class BlockWoodenDoor extends WoodenDoor implements IRedstone {
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
        if (($this->getDamage() & 0x08) === 0x08) {
            $up = $this;
            $down = $this->getSide(Facing::DOWN);
        } else {
            $up = $this->getSide(Facing::UP);
            $down = $this;
        }

        if ($this->isBlockPowered($up->asVector3()) || $this->isBlockPowered($down->asVector3())) {
            if (($up->getDamage() & 0x02) != 0x02 && ($down->getDamage() & 0x04) != 0x04) {
                $up->setDamage($up->getDamage() ^ 0x02);
                $down->setDamage($down->getDamage() ^ 0x04);
                $this->level->addSound(new DoorSound($this));
            } elseif (($up->getDamage() & 0x02) != 0x02) {
                $up->setDamage($up->getDamage() ^ 0x02);
            }
        } else {
            if (($up->getDamage() & 0x02) == 0x02 && ($down->getDamage() & 0x04) == 0x04) {
                $up->setDamage($up->getDamage() ^ 0x02);
                $down->setDamage($down->getDamage() ^ 0x04);
                $this->level->addSound(new DoorSound($this));
            }
        }

        $this->level->setBlock($up, $up, true);
        $this->level->setBlock($down, $down, true);
    }
}