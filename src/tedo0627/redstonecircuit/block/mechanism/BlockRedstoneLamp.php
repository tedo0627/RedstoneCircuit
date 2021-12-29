<?php

namespace tedo0627\redstonecircuit\block\mechanism;

use pocketmine\block\RedstoneLamp;
use tedo0627\redstonecircuit\block\BlockPowerHelper;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;

class BlockRedstoneLamp extends RedstoneLamp implements IRedstoneComponent {
    use RedstoneComponentTrait;

    public function onPostPlace(): void {
        if (BlockPowerHelper::isPowered($this) === $this->powered) return;

        $this->powered = !$this->powered;
        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
    }

    public function onRedstoneUpdate(): void {
        if (BlockPowerHelper::isPowered($this) === $this->powered) return;

        $this->powered = !$this->powered;
        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
    }
}