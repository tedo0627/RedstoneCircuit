<?php

namespace tedo0627\redstonecircuit\block\mechanism;

use pocketmine\block\RedstoneLamp;
use tedo0627\redstonecircuit\block\BlockPowerHelper;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\event\BlockRedstonePowerUpdateEvent;
use tedo0627\redstonecircuit\RedstoneCircuit;

class BlockRedstoneLamp extends RedstoneLamp implements IRedstoneComponent {
    use RedstoneComponentTrait;

    public function onPostPlace(): void {
        if (BlockPowerHelper::isPowered($this) === $this->isPowered()) return;

        $this->setPowered(!$this->isPowered());
        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
    }

    public function onScheduledUpdate(): void {
        $side = BlockPowerHelper::isPowered($this);
        if ($side) return;

        $this->updatePowered(false);
    }

    public function onRedstoneUpdate(): void {
        if (BlockPowerHelper::isPowered($this) === $this->isPowered()) return;

        if ($this->isPowered()) {
            $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 4);
            return;
        }

        $this->updatePowered(true);
    }

    protected function updatePowered(bool $powered): void {
        if ($powered === $this->isPowered()) return;

        if (RedstoneCircuit::isCallEvent()) {
            $event = new BlockRedstonePowerUpdateEvent($this, $powered, $this->isPowered());
            $event->call();
            $powered = $event->getNewPowered();
            if ($powered === $this->isPowered()) return;
        }

        $this->setPowered($powered);
        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
    }
}