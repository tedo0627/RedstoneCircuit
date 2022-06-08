<?php

namespace tedo0627\redstonecircuit\block\transmission;

use pocketmine\block\BlockLegacyIds;
use pocketmine\block\Cake;
use pocketmine\block\EndPortalFrame;
use pocketmine\block\ItemFrame;
use pocketmine\block\Jukebox;
use pocketmine\block\RedstoneComparator;
use pocketmine\block\tile\Container;
use pocketmine\block\utils\RecordType;
use pocketmine\inventory\CallbackInventoryListener;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use tedo0627\redstonecircuit\block\BlockPowerHelper;
use tedo0627\redstonecircuit\block\BlockUpdateHelper;
use tedo0627\redstonecircuit\block\ILinkRedstoneWire;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\IRedstoneDiode;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\event\BlockRedstoneSignalUpdateEvent;
use tedo0627\redstonecircuit\RedstoneCircuit;

class BlockRedstoneComparator extends RedstoneComparator implements IRedstoneComponent, ILinkRedstoneWire {
    use RedstoneComponentTrait;

    private ?CallbackInventoryListener $callBack = null;

    public function writeStateToWorld(): void {
        parent::writeStateToWorld();
        $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 1);
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null): bool {
        parent::onInteract($item, $face, $clickVector, $player);
        BlockUpdateHelper::updateDiodeRedstone($this, Facing::opposite($this->getFacing()));
        return true;
    }

    public function onBreak(Item $item, ?Player $player = null): bool {
        if ($this->callBack != null) {
            $block = $this->getSide($this->getFacing());
            $tile = $this->getPosition()->getWorld()->getTile($block->getPosition());
            if ($tile instanceof Container) {
                $inventory = $tile->getInventory();
                $inventory->getListeners()->remove($this->callBack);
            }

            if (BlockPowerHelper::isNormalBlock($block)) {
                $block = $this->getSide($this->getFacing(), 2);
                $tile = $this->getPosition()->getWorld()->getTile($block->getPosition());
                if ($tile instanceof Container) {
                    $inventory = $tile->getInventory();
                    $inventory->getListeners()->remove($this->callBack);
                }
            }
        }

        parent::onBreak($item, $player);
        BlockUpdateHelper::updateDiodeRedstone($this, Facing::opposite($this->getFacing()));
        return true;
    }

    public function onScheduledUpdate(): void {
        $power = $this->recalculateUtilityPower();
        if ($power === null) $power = BlockPowerHelper::getPower($this->getSide($this->getFacing()), $this->getFacing());

        $sidePower = 0;
        $face = Facing::rotateY($this->getFacing(), true);
        $side = $this->getSide($face);
        if ($side instanceof IRedstoneDiode || $side instanceof BlockRedstoneWire) {
            $sidePower = $side->getWeakPower($face);
        }

        $face = Facing::opposite($face);
        $side = $this->getSide($face);
        if ($side instanceof IRedstoneDiode || $side instanceof BlockRedstoneWire) {
            $sidePower = max($sidePower, $side->getWeakPower($face));
        }

        $power = $this->isSubtractMode() ? max(0, $power - $sidePower) : ($power >= $sidePower ? $power : 0);
        if ($this->getOutputSignalStrength() === $power) return;

        if (RedstoneCircuit::isCallEvent()) {
            $event = new BlockRedstoneSignalUpdateEvent($this, $power, $this->getOutputSignalStrength());
            $event->call();

            $power = $event->getNewSignal();
            if ($this->getOutputSignalStrength() == $power) return;
        }

        $this->setPowered($power > 0);
        $this->setOutputSignalStrength($power);
        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
        BlockUpdateHelper::updateDiodeRedstone($this, Facing::opposite($this->getFacing()));
    }

    private function recalculateUtilityPower(int $step = 1): ?int {
        $block = $this->getSide($this->getFacing(), $step);
        $tile = $this->getPosition()->getWorld()->getTile($block->getPosition());
        $power = 0;
        if ($tile instanceof Container) {
            $inventory = $tile->getInventory();
            $this->createCallBack($inventory);

            if (count($inventory->getContents()) != 0) {
                $stack = 0;
                for ($slot = 0; $slot < $inventory->getSize(); $slot++) {
                    $item = $inventory->getItem($slot);
                    if ($item->getId() === BlockLegacyIds::AIR) continue;
                    $stack += $item->getCount() / $item->getMaxStackSize();
                }
                $power = 1 + ($stack / $inventory->getSize()) * 14;
            }
            return $power;
        }
        $this->callBack = null;

        if ($step === 2) $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 1);

        if ($block instanceof Cake) return (7 - $block->getBites()) * 2;
        if ($block instanceof EndPortalFrame) return $block->hasEye() ? 15 : 0;
        if ($block instanceof Jukebox) {
            $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 1);
            $record = $block->getRecord();
            if ($record === null) return 0;

            return match ($record->getRecordType()) {
                RecordType::DISK_13() => 1,
                RecordType::DISK_CAT() => 2,
                RecordType::DISK_BLOCKS() => 3,
                RecordType::DISK_CHIRP() => 4,
                RecordType::DISK_FAR() => 5,
                RecordType::DISK_MALL() => 6,
                RecordType::DISK_MELLOHI() => 7,
                RecordType::DISK_STAL() => 8,
                RecordType::DISK_STRAD() => 9,
                RecordType::DISK_WARD() => 10,
                RecordType::DISK_11() => 11,
                RecordType::DISK_WAIT() => 12,
                default => 15
            };
        }
        if ($block instanceof ItemFrame) {
            if ($block->getFacing() !== $this->getFacing()) return 0;
            if ($block->getFramedItem() === null) return 0;
            return $block->getItemRotation() + 1;
        }

        if ($step === 1 && BlockPowerHelper::isNormalBlock($block)) return $this->recalculateUtilityPower(2);
        return null;
    }

    private function createCallBack(Inventory $inventory): void {
        if ($this->callBack === null) {
            $block = $this;
            $this->callBack = CallbackInventoryListener::onAnyChange(
                fn(Inventory $inventory) => $block->getPosition()->getWorld()->scheduleDelayedBlockUpdate($block->getPosition(), 1)
            );
        }

        $listeners = $inventory->getListeners();
        if ($listeners->contains($this->callBack)) return;

        $listeners->add($this->callBack);
    }

    public function getStrongPower(int $face): int {
        return $this->getWeakPower($face);
    }

    public function getWeakPower(int $face): int {
        return $this->isPowered() && $face === $this->getFacing() ? $this->getOutputSignalStrength() : 0;
    }

    public function isPowerSource(): bool {
        return $this->isPowered();
    }

    public function onRedstoneUpdate(): void {
        $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 1);
    }

    public function isConnect(int $face): bool {
        return true;
    }
}