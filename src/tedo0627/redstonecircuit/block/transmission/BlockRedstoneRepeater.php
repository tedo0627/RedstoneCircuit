<?php

namespace tedo0627\redstonecircuit\block\transmission;

use pocketmine\block\Block;
use pocketmine\block\RedstoneRepeater;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use tedo0627\redstonecircuit\block\BlockPowerHelper;
use tedo0627\redstonecircuit\block\BlockUpdateHelper;
use tedo0627\redstonecircuit\block\FlowablePlaceHelper;
use tedo0627\redstonecircuit\block\ILinkRedstoneWire;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\IRedstoneDiode;

class BlockRedstoneRepeater extends RedstoneRepeater implements IRedstoneComponent, ILinkRedstoneWire, IRedstoneDiode {

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool {
        if (!FlowablePlaceHelper::check($this, Facing::DOWN)) return false;
        if ($player != null) $this->setFacing(Facing::opposite($player->getHorizontalFacing()));
        $tx->addBlock($blockReplace->getPosition(), $this);
        return true;
    }

    public function onPostPlace(): void {
        $this->onRedstoneUpdate();
    }

    public function onBreak(Item $item, ?Player $player = null): bool {
        parent::onBreak($item, $player);
        BlockUpdateHelper::updateDiodeRedstone($this, Facing::opposite($this->getFacing()));
        return true;
    }

    public function onNearbyBlockChange(): void {
        if (FlowablePlaceHelper::check($this, Facing::DOWN)) {
            BlockUpdateHelper::updateDiodeRedstone($this, Facing::opposite($this->getFacing()));
        } else {
            $this->getPosition()->getWorld()->useBreakOn($this->getPosition());
        }
    }

    public function onScheduledUpdate(): void {
        if ($this->isLocked()) return;

        $powered = $this->isPowered();
        $side = BlockPowerHelper::isSidePowered($this, $this->getFacing());
        if ($powered && !$side) {
            $this->setPowered(false);
            $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
            BlockUpdateHelper::updateDiodeRedstone($this, Facing::opposite($this->getFacing()));
            return;
        }

        if (!$powered) {
            $this->setPowered(true);
            $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
            BlockUpdateHelper::updateDiodeRedstone($this, Facing::opposite($this->getFacing()));

            if (!$side) $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), $this->getDelay() * 2);
        }
    }

    public function isLocked(): bool {
        $face = Facing::rotateY($this->getFacing(), true);
        $block = $this->getSide($face);
        if ($block instanceof IRedstoneDiode && BlockPowerHelper::getStrongPower($block, $face)) return true;

        $face = Facing::opposite($face);
        $block = $this->getSide($face);
        return $block instanceof IRedstoneDiode && BlockPowerHelper::getStrongPower($block, $face);
    }

    public function getStrongPower(int $face): int {
        return $this->getWeakPower($face);
    }

    public function getWeakPower(int $face): int {
        return $this->isPowered() && $face == $this->getFacing() ? 15 : 0;
    }

    public function isPowerSource(): bool {
        return $this->isPowered();
    }

    public function onRedstoneUpdate(): void {
        if ($this->isLocked()) return;

        $side = BlockPowerHelper::isSidePowered($this, $this->getFacing());
        if ($side && !$this->isPowered()) {
            $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), $this->getDelay() * 2);
            return;
        }

        if ($side || !$this->isPowered()) return;
        $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), $this->getDelay() * 2);
    }

    public function isConnect(int $face): bool {
        return $face == $this->getFacing() || $face == Facing::opposite($this->getFacing());
    }
}