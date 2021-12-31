<?php

namespace tedo0627\redstonecircuit\block\power;

use pocketmine\block\RedstoneTorch;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\player\Player;
use tedo0627\redstonecircuit\block\BlockPowerHelper;
use tedo0627\redstonecircuit\block\BlockUpdateHelper;
use tedo0627\redstonecircuit\block\ILinkRedstoneWire;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\LinkRedstoneWireTrait;

class BlockRedstoneTorch extends RedstoneTorch implements IRedstoneComponent, ILinkRedstoneWire {
    use LinkRedstoneWireTrait;

    public function onPostPlace(): void {
        $this->onRedstoneUpdate();
        BlockUpdateHelper::updateDiodeRedstone($this, Facing::opposite($this->getFacing()));
    }

    public function onBreak(Item $item, ?Player $player = null): bool {
        parent::onBreak($item, $player);
        BlockUpdateHelper::updateDiodeRedstone($this, Facing::opposite($this->getFacing()));
        return true;
    }

    public function onNearbyBlockChange(): void {
        parent::onNearbyBlockChange();
        BlockUpdateHelper::updateDiodeRedstone($this, Facing::opposite($this->getFacing()));
    }

    public function onScheduledUpdate(): void {
        $this->setLit(!$this->isLit());
        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
        BlockUpdateHelper::updateDiodeRedstone($this, Facing::opposite($this->getFacing()));
    }

    public function getStrongPower(int $face): int {
        return $this->isLit() && $face === Facing::DOWN ? 15 : 0;
    }

    public function getWeakPower(int $face): int {
        return $this->isLit() && $face !== $this->getFacing() ? 15 : 0;
    }

    public function isPowerSource(): bool {
        return $this->isLit();
    }

    public function onRedstoneUpdate(): void {
        if (BlockPowerHelper::isSidePowered($this, Facing::opposite($this->getFacing())) !== $this->isLit()) return;
        $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 2);
    }
}