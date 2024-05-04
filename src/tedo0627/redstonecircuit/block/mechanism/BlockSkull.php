<?php

namespace tedo0627\redstonecircuit\block\mechanism;

use pocketmine\block\Skull;
use pocketmine\block\utils\SkullType;
use tedo0627\redstonecircuit\block\BlockPowerHelper;
use tedo0627\redstonecircuit\block\entity\BlockEntitySkull;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\event\BlockRedstonePowerUpdateEvent;
use tedo0627\redstonecircuit\RedstoneCircuit;

class BlockSkull extends Skull implements IRedstoneComponent {
    use RedstoneComponentTrait;

    private bool $mouthMoving = false;

    public function readStateFromWorld(): void {
        parent::readStateFromWorld();
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        if ($tile instanceof BlockEntitySkull) $this->setMouthMoving($tile->isMouthMoving());
    }

    public function writeStateToWorld(): void {
        parent::writeStateToWorld();
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        assert($tile instanceof BlockEntitySkull);
        $tile->setMouthMoving($this->isMouthMoving());
    }

    public function onRedstoneUpdate(): void {
        if ($this->getSkullType() !== SkullType::DRAGON()) return;

        $powered = BlockPowerHelper::isPowered($this);
        if ($powered === $this->isMouthMoving()) return;

        if (RedstoneCircuit::isCallEvent()) {
            $event = new BlockRedstonePowerUpdateEvent($this, $powered, $this->isMouthMoving());
            $event->call();
            $powered = $event->getNewPowered();
            if ($powered === $this->isMouthMoving()) return;
        }

        $this->setMouthMoving($powered);
        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
    }

    public function isMouthMoving(): bool {
        return $this->mouthMoving;
    }

    public function setMouthMoving(bool $mouthMoving): void {
        $this->mouthMoving = $mouthMoving;
    }
}