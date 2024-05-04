<?php

namespace tedo0627\redstonecircuit\block\mechanism;

use pocketmine\block\Hopper;
use pocketmine\block\inventory\FurnaceInventory;
use pocketmine\block\Jukebox as BlockJukebox;
use pocketmine\block\tile\Container;
use pocketmine\block\tile\Furnace;
use pocketmine\block\tile\Jukebox;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Bucket;
use pocketmine\item\Record;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\Server;
use tedo0627\redstonecircuit\block\BlockPowerHelper;
use tedo0627\redstonecircuit\block\entity\BlockEntityHopper;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\event\BlockRedstonePowerUpdateEvent;
use tedo0627\redstonecircuit\event\HopperMoveItemEvent;
use tedo0627\redstonecircuit\event\HopperPickupItemEvent;
use tedo0627\redstonecircuit\RedstoneCircuit;

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
        if (!$this->isPowered()) $this->suckEntity();
        if ($this->getTransferCooldown() > 0) {
            $this->writeStateToWorld();
            return;
        }

        $this->setTransferCooldown(0);
        if ($this->isPowered()) {
            $this->writeStateToWorld();
            return;
        }

        $check = $this->ejectItem();
        $check |= $this->suckItem();
        if ($check) $this->setTransferCooldown(8);
        $this->writeStateToWorld();
    }

    protected function ejectItem(): bool {
        $hopper = $this->getPosition()->getWorld()->getTile($this->getPosition());
        if (!$hopper instanceof BlockEntityHopper) return false;

        $target = $this->getPosition()->getWorld()->getTile($this->getSide($this->getFacing())->getPosition());
        $juke = $target instanceof Jukebox;
        if (!$target instanceof Container && !$juke) return false;

        $furnace = $target instanceof Furnace && $this->getFacing() !== Facing::DOWN;

        $inventory = $hopper->getInventory();
        $slot = null;
        $item = null;
        for ($i = 0; $i < $inventory->getSize(); $i++) {
            $ejectItem = $inventory->getItem($i);
            if ($ejectItem->isNull()) continue;
            if ($juke && !$ejectItem instanceof Record) continue;
            if ($furnace && $ejectItem->getFuelTime() <= 0) continue;

            $slot = $i;
            $item = $ejectItem;
            break;
        }
        if ($slot === null) return false;

        $pop = $item->pop();
        if ($target instanceof Jukebox) {
            $targetBlock = $target->getBlock();
            if (!$targetBlock instanceof BlockJukebox) return false;
            if ($targetBlock->getRecord() !== null) return false;
            if (!$pop instanceof Record) return false;

            if (RedstoneCircuit::isCallEvent()) {
                $event = new HopperMoveItemEvent($this, $inventory, $targetBlock, clone $pop);
                $event->call();
                if ($event->isCancelled()) return false;
            }

            $targetBlock->insertRecord($pop);
            $targetBlock->writeStateToWorld();
            $inventory->setItem($slot, $item);
            return true;
        }

        $targetInventory = $target->getInventory();
        if ($targetInventory instanceof FurnaceInventory) {
            $targetSlot = $this->getFacing() === Facing::DOWN ? 0 : 1;
            if ($targetSlot === 1 && $pop->getFuelTime() <= 0) return false;

            $targetItem = $targetInventory->getItem($targetSlot);
            if ($targetItem->isNull()) {
                $targetInventory->setItem($targetSlot, $pop);
                $inventory->setItem($slot, $item);
                return true;
            }

            $count = $targetItem->getCount() + $pop->getCount();
            if ($targetItem->canStackWith($pop) && $count <= $targetItem->getMaxStackSize()) {
                $targetItem->setCount($count);
                $targetInventory->setItem($targetSlot, $targetItem);
                $inventory->setItem($slot, $item);
                return true;
            }

            return false;
        }

        if (!$targetInventory->canAddItem($pop)) return false;

        if (RedstoneCircuit::isCallEvent()) {
            $event = new HopperMoveItemEvent($this, $inventory, $targetInventory, clone $pop);
            $event->call();
            if ($event->isCancelled()) return false;
        }

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

        $sourceInventory = $source->getInventory();
        $slot = null;
        $item = null;
        if ($sourceInventory instanceof FurnaceInventory) {
            $fuel = $sourceInventory->getFuel();
            if ($fuel instanceof Bucket) {
                $slot = 1;
                $item = $fuel;
            } else {
                $result = $sourceInventory->getResult();
                if (!$result->isNull()) {
                    $slot = 2;
                    $item = $result;
                }
            }
        } else {
            for ($i = 0; $i < $sourceInventory->getSize(); $i++) {
                $suckItem = $sourceInventory->getItem($i);
                if ($suckItem->isNull()) continue;

                $slot = $i;
                $item = $suckItem;
                break;
            }
        }
        if ($slot === null) return false;

        $hopper = $this->getPosition()->getWorld()->getTile($this->getPosition());
        if (!$hopper instanceof BlockEntityHopper) return false;

        $pop = $item->pop();
        $inventory = $hopper->getInventory();
        if (!$inventory->canAddItem($pop)) return false;

        if (RedstoneCircuit::isCallEvent()) {
            $event = new HopperMoveItemEvent($this, $sourceInventory, $inventory, clone $pop);
            $event->call();
            if ($event->isCancelled()) return false;
        }

        $inventory->addItem($pop);
        $sourceInventory->setItem($slot, $item);
        return true;
    }

    protected function suckEntity(): bool {
        $hopper = $this->getPosition()->getWorld()->getTile($this->getPosition());
        if (!$hopper instanceof BlockEntityHopper) return false;

        $inventory = $hopper->getInventory();
        $pos = $this->getPosition();
        $bb = new AxisAlignedBB($pos->getFloorX(), $pos->getFloorY() + 1, $pos->getFloorZ(), $pos->getFloorX() + 1, $pos->getFloorY() + 2, $pos->getFloorZ() + 1);
        $entities = $this->getPosition()->getWorld()->getNearbyEntities($bb);
        $check = false;
        for ($i = 0; $i < count($entities); $i++) {
            $entity = $entities[$i];
            if (!$entity instanceof ItemEntity) continue;

            $source = clone $entity->getItem();
            $count = $inventory->getAddableItemQuantity($source);
            if ($count === 0) continue;

            $pop = $source->pop($count);
            if (RedstoneCircuit::isCallEvent()) {
                $event = new HopperPickupItemEvent($this, $inventory, $entity, clone $pop);
                $event->call();
                if ($event->isCancelled()) continue;
            }

            $inventory->addItem($pop);
            $entity->getItem()->pop($count);
            if ($source->getCount() === 0) $entity->flagForDespawn();
            $check = true;
        }
        return $check;
    }

    public function onRedstoneUpdate(): void {
        $powered = BlockPowerHelper::isPowered($this);
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