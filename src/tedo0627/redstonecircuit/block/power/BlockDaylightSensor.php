<?php

namespace tedo0627\redstonecircuit\block\power;

use pocketmine\block\DaylightSensor;
use tedo0627\redstonecircuit\block\BlockUpdateHelper;
use tedo0627\redstonecircuit\block\ILinkRedstoneWire;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\LinkRedstoneWireTrait;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;

class BlockDaylightSensor extends DaylightSensor implements IRedstoneComponent, ILinkRedstoneWire {
    use LinkRedstoneWireTrait;
    use RedstoneComponentTrait;

    public function onScheduledUpdate(): void {
        $signal = $this->getOutputSignalStrength();
        parent::onScheduledUpdate();
        if ($signal !== $this->getOutputSignalStrength()) BlockUpdateHelper::updateAroundRedstone($this);
    }

    public function getWeakPower(int $face): int {
        return $this->getOutputSignalStrength();
    }

    public function isPowerSource(): bool {
        return $this->getOutputSignalStrength() > 0;
    }
}