<?php

namespace tedo0627\redstonecircuit\block\power;

use pocketmine\block\TrappedChest;
use pocketmine\block\utils\AnalogRedstoneSignalEmitterTrait;
use pocketmine\math\Facing;
use tedo0627\redstonecircuit\block\BlockUpdateHelper;
use tedo0627\redstonecircuit\block\entity\BlockEntityChest;
use tedo0627\redstonecircuit\block\ILinkRedstoneWire;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\LinkRedstoneWireTrait;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;

class BlockTrappedChest extends TrappedChest implements IRedstoneComponent, ILinkRedstoneWire {
    use AnalogRedstoneSignalEmitterTrait;
    use LinkRedstoneWireTrait;
    use RedstoneComponentTrait;

    public function readStateFromWorld(): void {
        parent::readStateFromWorld();
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        if ($tile instanceof BlockEntityChest) {
            $this->setOutputSignalStrength(min($tile->getInventory()->getViewerCount(), 15));
        }
    }

    public function onScheduledUpdate(): void {
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        if (!$tile instanceof BlockEntityChest) return;

        $this->setOutputSignalStrength(min($tile->getInventory()->getViewerCount(), 15));
        BlockUpdateHelper::updateAroundDirectionRedstone($this, Facing::DOWN);
    }

    public function getStrongPower(int $face): int {
        return $face === Facing::UP ? $this->getOutputSignalStrength() : 0;
    }

    public function getWeakPower(int $face): int {
        return $this->getOutputSignalStrength();
    }

    public function isPowerSource(): bool {
        return $this->getOutputSignalStrength() !== 0;
    }
}