<?php

namespace tedo0627\redstonecircuit\block\mechanism;

use pocketmine\block\RedstoneLamp;
use tedo0627\redstonecircuit\block\BlockPowerHelper;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;

class BlockRedstoneLamp extends RedstoneLamp implements IRedstoneComponent {
    use RedstoneComponentTrait;

    public function onPostPlace(): void {
        if (BlockPowerHelper::isPowered($this) === $this->isPowered()) return;

        $this->setPowered(!$this->isPowered());
        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
    }

    public function onScheduledUpdate(): void {
        $this->setPowered(false);
        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
    }

    public function onRedstoneUpdate(): void {
        if (BlockPowerHelper::isPowered($this) === $this->isPowered()) return;

        if ($this->isPowered()) {
            $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 4);
            return;
        }

        $this->setPowered(true);
        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
    }
}