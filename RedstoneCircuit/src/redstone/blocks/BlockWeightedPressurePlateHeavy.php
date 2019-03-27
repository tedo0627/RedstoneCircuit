<?php

namespace redstone\blocks;

use redstone\utils\Facing;

class BlockWeightedPressurePlateHeavy extends BlockPressurePlateBase {

    protected $id = self::HEAVY_WEIGHTED_PRESSURE_PLATE;
    
    public function getName() : string {
        return "Heavy Weighted Pressure Plate";
    }

    public function computeDamage() : int {
        $count = count($this->getLevel()->getNearbyEntities($this->bb()));
        $count += 9;
        if ($count > 150) {
            $count = 150;
        }
        return floor($count / 10);
    }

    public function getDelay() : int {
        return 8;
    }

    public function getOnSoundExtraData() : int {
        return 829;
    }

    public function getOffSoundExtraData() : int {
        return 1525;
    }

    public function getStrongPower(int $face) : int {
        if (!$this->isPowerSource()) {
            return 0;
        }
        if ($face == Facing::UP) {
            return $this->getDamage();
        }
        return 0;
    }

    public function getWeakPower(int $face) : int {
        if (!$this->isPowerSource()) {
            return 0;
        }
        return $this->getDamage();
    }
}