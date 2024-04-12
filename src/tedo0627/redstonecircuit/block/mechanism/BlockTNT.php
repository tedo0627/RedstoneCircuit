<?php

namespace tedo0627\redstonecircuit\block\mechanism;

use pocketmine\block\TNT;
use tedo0627\redstonecircuit\block\BlockPowerHelper;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\CustomConfig;
use tedo0627\redstonecircuit\event\BlockRedstonePowerUpdateEvent;

class BlockTNT extends TNT implements IRedstoneComponent {
    use RedstoneComponentTrait;

    public function onRedstoneUpdate(): void {
        if (!BlockPowerHelper::isPowered($this)) return;

        if (CustomConfig::isCallEvent()) {
            $event = new BlockRedstonePowerUpdateEvent($this, true, false);
            $event->call();
            if (!$event->getNewPowered()) return;
        }
        $this->ignite();
    }
}