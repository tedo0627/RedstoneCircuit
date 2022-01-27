<?php

namespace tedo0627\redstonecircuit\block\mechanism;

use pocketmine\block\Hopper;
use pocketmine\block\tile\Container;
use pocketmine\math\Facing;
use pocketmine\Server;
use tedo0627\redstonecircuit\block\BlockPowerHelper;
use tedo0627\redstonecircuit\block\entity\BlockEntityHopper;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;

class BlockHopper extends Hopper implements IRedstoneComponent {
    use RedstoneComponentTrait;

    private int $transferCooldown = 0;
    private int $tickedGameTime = 0;

    public function readStateFromWorld(): void {
        parent::readStateFromWorld();
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        if (!$tile instanceof BlockEntityHopper) return;

        $this->setTransferCooldown($tile->getTransferCooldown());
        $this->setTickedGameTime($tile->getTickedGameTime());
    }

    public function writeStateToWorld(): void {
        parent::writeStateToWorld();
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        assert($tile instanceof BlockEntityHopper);
        $tile->setTransferCooldown($this->getTransferCooldown());
        $tile->setTickedGameTime($this->getTickedGameTime());
    }

    public function onScheduledUpdate(): void {
        $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 1);

        $this->setTransferCooldown($this->getTransferCooldown() - 1);
        $this->setTickedGameTime(Server::getInstance()->getTick());
        if ($this->getTransferCooldown() > 0) {
            $this->writeStateToWorld();
            return;
        }

        $this->setTransferCooldown(0);
        if ($this->isPowered()) {
            $this->writeStateToWorld();
            return;
        }

        if ($this->ejectItem() || $this->suckItem()) $this->setTransferCooldown(8);
        $this->writeStateToWorld();
    }

    protected function ejectItem(): bool {
        $hopper = $this->getPosition()->getWorld()->getTile($this->getPosition());
        if (!$hopper instanceof BlockEntityHopper) return false;

        $inventory = $hopper->getRealInventory();
        $slot = null;
        $item = null;
        for ($i = 0; $i < $inventory->getSize(); $i++) {
            $ejectItem = $inventory->getItem($i);
            if ($ejectItem->isNull()) continue;

            $slot = $i;
            $item = $ejectItem;
            break;
        }
        if ($slot === null) return false;

        $target = $this->getPosition()->getWorld()->getTile($this->getSide($this->getFacing())->getPosition());
        if (!$target instanceof Container) return false;

        $pop = $item->pop();
        $targetInventory = $target->getRealInventory();
        if (!$targetInventory->canAddItem($pop)) return false;

        $targetInventory->addItem($pop);
        $inventory->setItem($slot, $item);

        $block = $target->getBlock();
        if (!$block instanceof BlockHopper) return true;

        $block->setTransferCooldown($block->getTickedGameTime() >= $this->getTickedGameTime() ? 7 : 8);
        $block->writeStateToWorld();
        return true;
    }

    protected function suckItem(): bool {
        $source = $this->getPosition()->getWorld()->getTile($this->getSide(Facing::UP)->getPosition());
        if (!$source instanceof Container) return false;

        $sourceInventory = $source->getRealInventory();
        $slot = null;
        $item = null;
        for ($i = 0; $i < $sourceInventory->getSize(); $i++) {
            $suckItem = $sourceInventory->getItem($i);
            if ($suckItem->isNull()) continue;

            $slot = $i;
            $item = $suckItem;
            break;
        }
        if ($slot === null) return false;

        $hopper = $this->getPosition()->getWorld()->getTile($this->getPosition());
        if (!$hopper instanceof BlockEntityHopper) return false;

        $pop = $item->pop();
        $inventory = $hopper->getRealInventory();
        if (!$inventory->canAddItem($pop)) return false;

        $inventory->addItem($pop);
        $sourceInventory->setItem($slot, $item);
        return true;
    }

    public function onRedstoneUpdate(): void {
        $powered = BlockPowerHelper::isPowered($this);
        if ($powered && !$this->isPowered()) {
            $this->setPowered(true);
            $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
            return;
        }

        if ($powered || !$this->isPowered()) return;

        $this->setPowered(false);
        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
        $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 1);
    }

    public function getTransferCooldown(): int {
        return $this->transferCooldown;
    }

    public function setTransferCooldown(int $cooldown): void {
        $this->transferCooldown = $cooldown;
    }

    public function getTickedGameTime(): int {
        return $this->tickedGameTime;
    }

    public function setTickedGameTime(int $tick): void {
        $this->tickedGameTime = $tick;
    }
}