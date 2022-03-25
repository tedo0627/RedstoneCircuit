<?php

namespace tedo0627\redstonecircuit\block\power;

use pocketmine\block\Block;
use pocketmine\block\Opaque;
use pocketmine\block\utils\AnyFacingTrait;
use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\block\utils\PoweredByRedstoneTrait;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use tedo0627\redstonecircuit\block\BlockEntityInitializeTrait;
use tedo0627\redstonecircuit\block\BlockUpdateHelper;
use tedo0627\redstonecircuit\block\entity\BlockEntityObserver;
use tedo0627\redstonecircuit\block\ILinkRedstoneWire;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\event\BlockRedstonePowerUpdateEvent;
use tedo0627\redstonecircuit\RedstoneCircuit;

class BlockObserver extends Opaque implements IRedstoneComponent, ILinkRedstoneWire {
    use AnyFacingTrait;
    use BlockEntityInitializeTrait;
    use PoweredByRedstoneTrait;
    use RedstoneComponentTrait;

    protected int $blockId = -1;
    protected int $stateMeta = -1;

    protected function writeStateToMeta(): int {
        return BlockDataSerializer::writeFacing($this->facing) |
            ($this->isPowered() ? 0x08 : 0);
    }

    public function readStateFromData(int $id, int $stateMeta): void {
        $this->setFacing(BlockDataSerializer::readFacing($stateMeta & 0x07));
        $this->setPowered(($stateMeta & 0x08) !== 0);
    }

    public function readStateFromWorld(): void {
        parent::readStateFromWorld();
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        if($tile instanceof BlockEntityObserver) {
            $this->setSideBlockId($tile->getBlockId());
            $this->setSideStateMeta($tile->getStateMeta());
            $this->setInitialized($tile->isInitialized());
        }
    }

    public function writeStateToWorld(): void {
        parent::writeStateToWorld();
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        assert($tile instanceof BlockEntityObserver);
        $tile->setBlockId($this->getSideBlockId());
        $tile->setStateMeta($this->getSideStateMeta());
        $tile->setInitialized($this->isInitialized());
    }

    public function getStateBitmask(): int {
        return 0b1111;
    }

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool {
        if ($player !== null) {
            $x = abs($player->getLocation()->getFloorX() - $this->getPosition()->getX());
            $y = $player->getLocation()->getFloorY() - $this->getPosition()->getY();
            $z = abs($player->getLocation()->getFloorZ() - $this->getPosition()->getZ());
            if ($y > 0 && $x < 2 && $z < 2) {
                $this->setFacing(Facing::DOWN);
            } elseif ($y < -1 && $x < 2 && $z < 2) {
                $this->setFacing(Facing::UP);
            } else {
                $this->setFacing($player->getHorizontalFacing());
            }
        }
        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

    public function onBreak(Item $item, ?Player $player = null): bool {
        parent::onBreak($item, $player);
        BlockUpdateHelper::updateDiodeRedstone($this, Facing::opposite($this->getFacing()));
        return true;
    }

    public function onNearbyBlockChange(): void {
        $block = $this->getSide($this->getFacing());
        $id = $block->getId();
        $state = $block->getMeta();

        if ($this->getSideBlockId() === $id && $this->getSideStateMeta() === $state) return;

        $this->setSideBlockId($id);
        $this->setSideStateMeta($state);
        $this->setInitialized(true);
        $this->writeStateToWorld();
        $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 2);
    }

    public function onScheduledUpdate(): void {
        if (!$this->isInitialized()) {
            $this->setInitialized(true);
            $this->writeStateToWorld();
            return;
        }

        $powered = !$this->isPowered();
        if (RedstoneCircuit::isCallEvent()) {
            $event = new BlockRedstonePowerUpdateEvent($this, $powered, !$powered);
            $event->call();
            $powered = $event->getNewPowered();
        }
        $this->setPowered($powered);
        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
        BlockUpdateHelper::updateDiodeRedstone($this, Facing::opposite($this->getFacing()));

        if ($this->isPowered()) $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 4);
    }

    public function getSideBlockId(): int {
        return $this->blockId;
    }

    public function setSideBlockId(int $id): void {
        $this->blockId = $id;
    }

    public function getSideStateMeta(): int {
        return $this->stateMeta;
    }

    public function setSideStateMeta(int $meta): void {
        $this->stateMeta = $meta;
    }

    public function getStrongPower(int $face): int {
        return $this->isPowered() && $this->getFacing() === $face ? 15 : 0;
    }

    public function getWeakPower(int $face): int {
        return $this->isPowered() && $this->getFacing() === $face ? 15 : 0;
    }

    public function isPowerSource(): bool {
        return $this->isPowered();
    }

    public function isConnect(int $face): bool {
        return $this->getFacing() === $face;
    }
}