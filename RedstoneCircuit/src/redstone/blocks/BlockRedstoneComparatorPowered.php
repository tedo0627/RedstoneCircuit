<?php

namespace redstone\blocks;

use redstone\utils\Facing;

class BlockRedstoneComparatorPowered extends BlockRedstoneComparatorUnpowered {

    protected $id = self::POWERED_COMPARATOR;

    public function getName() : string {
        return "Powered Comparator";
    }

	public function onScheduledUpdate() : void {
        if ($this->getOutputPower() <= 0) {
            $this->getLevel()->setBlock($this, new BlockRedstoneComparatorUnpowered($this->getDamage()));
        }
        
        $this->updateAroundRedstone($this);
        $direction = Facing::ALL;
        for ($i = 0; $i < count($direction); ++$i) {
            $this->updateAroundRedstone($this->asVector3()->getSide($direction[$i]));
        }
	}

    public function getStrongPower(int $face) : int {
        return $this->getWeakPower($face);
    }

    public function getWeakPower(int $face) : int {
		if ($face == $this->getInputFace()) {
			return $this->getOutputPower();
        }
        return 0;
    }

    public function isPowerSource() : bool {
        return true;
    }

    public function onRedstoneUpdate() : void {
        $this->level->scheduleDelayedBlockUpdate($this, 2);
	}
}