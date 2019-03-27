<?php

namespace redstone\blocks;

use redstone\utils\Facing;

class BlockWeightedPressurePlateLight extends BlockPressurePlateBase {

    protected $id = self::LIGHT_WEIGHTED_PRESSURE_PLATE;
    
    public function getName() : string {
        return "Light Weighted Pressure Plate";
    }

    public function computeDamage() : int {
        $count = count($this->getLevel()->getNearbyEntities($this->bb()));
        if ($count > 15) {
            $count = 15;
        }
        return $count;
    }

    public function getDelay() : int {
        return 8;
    }

    public function getOnSoundExtraData() : int {
        return 1004;
    }

    public function getOffSoundExtraData() : int {
        return 3379;
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