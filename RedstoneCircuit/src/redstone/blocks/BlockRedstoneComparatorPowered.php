<?php

namespace redstone\blocks;

class BlockRedstoneComparatorPowered extends BlockRedstoneComparatorUnpowered {

    protected $id = self::POWERED_COMPARATOR;

    public function getName() : string {
        return "Powered Comparator";
    }

    public function onScheduledUpdate() : void {
        $comparator = $this->getBlockEntity();
        $power = $comparator->recalculateOutputPower();
        $comparator->setOutputSignal($power);

        if ($this->getOutputPower() <= 0) {
            $this->getLevel()->setBlock($this, new BlockRedstoneComparatorUnpowered($this->getDamage()));
        }
        $this->updateAroundDiodeRedstone($this);
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
}
