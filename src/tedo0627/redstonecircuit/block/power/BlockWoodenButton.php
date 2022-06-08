<?php

namespace tedo0627\redstonecircuit\block\power;

use pocketmine\block\WoodenButton;
use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use tedo0627\redstonecircuit\block\BlockUpdateHelper;
use tedo0627\redstonecircuit\block\ILinkRedstoneWire;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\LinkRedstoneWireTrait;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\event\BlockRedstonePowerUpdateEvent;
use tedo0627\redstonecircuit\RedstoneCircuit;

class BlockWoodenButton extends WoodenButton implements IRedstoneComponent, ILinkRedstoneWire {
    use LinkRedstoneWireTrait;
    use RedstoneComponentTrait;

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null): bool {
        if ($this->isPressed()) return true;

        if (RedstoneCircuit::isCallEvent()) {
            $event = new BlockRedstonePowerUpdateEvent($this, !$this->isPressed(), $this->isPressed());
            $event->call();
        }
        parent::onInteract($item, $face, $clickVector, $player);
        BlockUpdateHelper::updateAroundDirectionRedstone($this, Facing::opposite($this->getFacing()));
        return true;
    }

    public function onScheduledUpdate(): void {
        if (!$this->isPressed()) return;

        $entities = $this->getPosition()->getWorld()->getNearbyEntities($this->getHitCollision());
        for ($i = 0; $i < count($entities); $i++) {
            if ($entities[$i] instanceof Arrow) return;
        }

        if (RedstoneCircuit::isCallEvent()) {
            $event = new BlockRedstonePowerUpdateEvent($this, !$this->isPressed(), $this->isPressed());
            $event->call();
        }
        parent::onScheduledUpdate();
        BlockUpdateHelper::updateAroundDirectionRedstone($this, Facing::opposite($this->getFacing()));
    }

    public function onBreak(Item $item, ?Player $player = null): bool {
        parent::onBreak($item, $player);
        if ($this->isPressed()) BlockUpdateHelper::updateAroundDirectionRedstone($this, Facing::opposite($this->getFacing()));
        return true;
    }

    public function hasEntityCollision(): bool {
        return true;
    }

    public function onEntityInside(Entity $entity): bool {
        if (!$entity instanceof Arrow) return true;
        if (!$this->getHitCollision()->intersectsWith($entity->getBoundingBox())) return true;

        $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 1);
        if ($this->isPressed()) return true;

        if (RedstoneCircuit::isCallEvent()) {
            $event = new BlockRedstonePowerUpdateEvent($this, !$this->isPressed(), $this->isPressed());
            $event->call();
        }
        $this->setPressed(true);
        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
        BlockUpdateHelper::updateAroundDirectionRedstone($this, Facing::opposite($this->getFacing()));
        return false;
    }

    protected function getHitCollision(): AxisAlignedBB {
        $bb = match ($this->getFacing()) {
            Facing::DOWN => new AxisAlignedBB(5, 14, 6, 11, 16, 10),
            Facing::UP => new AxisAlignedBB(5, 0, 6, 11, 2, 10),
            Facing::NORTH => new AxisAlignedBB(5, 6, 14, 11, 10, 16),
            Facing::SOUTH => new AxisAlignedBB(5, 6, 0, 11, 10, 2),
            Facing::WEST => new AxisAlignedBB(14, 6, 5, 16, 10, 11),
            Facing::EAST => new AxisAlignedBB(0, 6, 5, 2, 10, 11)
        };
        $bb->minX /= 16;
        $bb->maxX /= 16;
        $bb->minY /= 16;
        $bb->maxY /= 16;
        $bb->minZ /= 16;
        $bb->maxZ /= 16;
        $pos = $this->getPosition();
        $bb->offset($pos->getX(), $pos->getY(), $pos->getZ());
        return $bb;
    }

    protected function recalculateCollisionBoxes(): array {
        $bb = match ($this->getFacing()) {
            Facing::DOWN => new AxisAlignedBB(5, 15, 6, 11, 16, 10),
            Facing::UP => new AxisAlignedBB(5, 0, 6, 11, 1, 10),
            Facing::NORTH => new AxisAlignedBB(5, 6, 15, 11, 10, 16),
            Facing::SOUTH => new AxisAlignedBB(5, 6, 0, 11, 10, 1),
            Facing::WEST => new AxisAlignedBB(15, 6, 5, 16, 10, 11),
            Facing::EAST => new AxisAlignedBB(0, 6, 5, 1, 10, 11)
        };
        $bb->minX /= 16;
        $bb->maxX /= 16;
        $bb->minY /= 16;
        $bb->maxY /= 16;
        $bb->minZ /= 16;
        $bb->maxZ /= 16;
        return [ $bb ];
    }

    public function getStrongPower(int $face): int {
        if (!$this->isPressed()) return 0;
        return $face === $this->getFacing() ? 15 : 0;
    }

    public function getWeakPower(int $face): int {
        return $this->isPressed() ? 15 : 0;
    }

    public function isPowerSource(): bool {
        return $this->isPressed();
    }
}