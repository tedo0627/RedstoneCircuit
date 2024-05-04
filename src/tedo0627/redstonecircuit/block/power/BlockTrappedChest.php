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
use tedo0627\redstonecircuit\event\BlockRedstoneSignalUpdateEvent;
use tedo0627\redstonecircuit\RedstoneCircuit;

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

        $signal = min($tile->getInventory()->getViewerCount(), 15);
        if ($this->getOutputSignalStrength() === $signal) return;

        if (RedstoneCircuit::isCallEvent()) {
            $event = new BlockRedstoneSignalUpdateEvent($this, $signal, $this->getOutputSignalStrength());
            $event->call();
            $signal = $event->getNewSignal();
            if ($this->getOutputSignalStrength() === $signal) return;
        }
        $this->setOutputSignalStrength($signal);
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