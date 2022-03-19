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
use tedo0627\redstonecircuit\event\BlockRedstonePowerUpdateEvent;
use tedo0627\redstonecircuit\RedstoneCircuit;

class BlockRedstoneTorch extends RedstoneTorch implements IRedstoneComponent, ILinkRedstoneWire {
    use LinkRedstoneWireTrait;

    public function onPostPlace(): void {
        $this->onRedstoneUpdate();
        BlockUpdateHelper::updateAroundDirectionRedstone($this, Facing::UP);
    }

    public function onBreak(Item $item, ?Player $player = null): bool {
        parent::onBreak($item, $player);
        BlockUpdateHelper::updateAroundDirectionRedstone($this, Facing::UP);
        return true;
    }

    public function onScheduledUpdate(): void {
        $lit = !$this->isLit();
        if (RedstoneCircuit::isCallEvent()) {
            $event = new BlockRedstonePowerUpdateEvent($this, $lit, $lit);
            $event->call();
            $lit = $event->getNewPowered();
        }
        $this->setLit($lit);
        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
        BlockUpdateHelper::updateAroundDirectionRedstone($this, Facing::UP);
    }

    public function getStrongPower(int $face): int {
        return $this->isLit() && $face === Facing::DOWN ? 15 : 0;
    }

    public function getWeakPower(int $face): int {
        if  (!$this->isLit()) return 0;
        if ($face === Facing::DOWN) return $this->getFacing() !== Facing::DOWN ? 15 : 0;
        return $face !== $this->getFacing() ? 15 : 0;
    }

    public function isPowerSource(): bool {
        return $this->isLit();
    }

    public function onRedstoneUpdate(): void {
        if (BlockPowerHelper::isSidePowered($this, Facing::opposite($this->getFacing())) !== $this->isLit()) return;
        $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 2);
    }
}