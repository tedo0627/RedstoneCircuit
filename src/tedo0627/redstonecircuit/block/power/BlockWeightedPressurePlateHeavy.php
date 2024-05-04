<?php

namespace tedo0627\redstonecircuit\block\power;

use pocketmine\block\Block;
use pocketmine\block\utils\SupportType;
use pocketmine\block\WeightedPressurePlateHeavy;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\player\Player;
use pocketmine\world\sound\RedstonePowerOffSound;
use pocketmine\world\sound\RedstonePowerOnSound;
use tedo0627\redstonecircuit\block\BlockUpdateHelper;
use tedo0627\redstonecircuit\block\ILinkRedstoneWire;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\LinkRedstoneWireTrait;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\event\BlockRedstoneSignalUpdateEvent;
use tedo0627\redstonecircuit\RedstoneCircuit;

class BlockWeightedPressurePlateHeavy extends WeightedPressurePlateHeavy implements IRedstoneComponent, ILinkRedstoneWire {
    use LinkRedstoneWireTrait;
    use RedstoneComponentTrait;

    public function onBreak(Item $item, ?Player $player = null): bool {
        parent::onBreak($item, $player);
        BlockUpdateHelper::updateAroundDirectionRedstone($this, Facing::DOWN);
        return true;
    }

    public function onNearbyBlockChange(): void {
        if ($this->canBeSupportedBy($this->getSide(Facing::DOWN))) return;
        $this->getPosition()->getWorld()->useBreakOn($this->getPosition());
    }

    public function onScheduledUpdate(): void {
        if ($this->getOutputSignalStrength() === 0) return;

        $entities = $this->getPosition()->getWorld()->getNearbyEntities($this->getHitCollision());
        $count = count($entities);
        if ($count !== 0) {
            $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 20);
        }

        $oldPower = $this->getOutputSignalStrength();
        $power = min((int) (($count + 9) / 10), 15);
        if ($oldPower === $power) return;

        if (RedstoneCircuit::isCallEvent()) {
            $event = new BlockRedstoneSignalUpdateEvent($this, $power, $oldPower);
            $event->call();
            $power = $event->getNewSignal();
            if ($oldPower === $power) return;
        }

        if ($power === 0) {
            $this->getPosition()->getWorld()->addSound($this->getPosition()->add(0.5, 0.5, 0.5), new RedstonePowerOffSound());
        }
        $this->setOutputSignalStrength($power);
        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
        BlockUpdateHelper::updateAroundDirectionRedstone($this, Facing::DOWN);
    }

    public function onEntityInside(Entity $entity): bool {
        if ($entity instanceof Player && $entity->isSpectator()) return true;

        $entities = $this->getPosition()->getWorld()->getNearbyEntities($this->getHitCollision());
        $count = count($entities);
        if ($count <= 0) return true;

        $oldPower = $this->getOutputSignalStrength();
        $power = min((int) (($count + 9) / 10), 15);
        if ($oldPower !== $power && RedstoneCircuit::isCallEvent()) {
            $event = new BlockRedstoneSignalUpdateEvent($this, $power, $oldPower);
            $event->call();
            $power = $event->getNewSignal();
            if ($oldPower === $power) return true;
        }

        if ($oldPower === 0) {
            $this->getPosition()->getWorld()->addSound($this->getPosition()->add(0.5, 0.5, 0.5), new RedstonePowerOnSound());
        }
        $this->setOutputSignalStrength($power);
        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
        BlockUpdateHelper::updateAroundDirectionRedstone($this, Facing::DOWN);
        $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 10);
        return true;
    }

    public function hasEntityCollision(): bool {
        return true;
    }

    protected function getHitCollision(): AxisAlignedBB {
        return new AxisAlignedBB(
            $this->getPosition()->getX() + 0.0625,
            $this->getPosition()->getY(),
            $this->getPosition()->getZ() + 0.0625,
            $this->getPosition()->getX() + 0.9375,
            $this->getPosition()->getY() + 0.0625,
            $this->getPosition()->getZ() + 0.9375
        );
    }

    public function getStrongPower(int $face): int {
        return $face == Facing::UP ? $this->getOutputSignalStrength() : 0;
    }

    public function getWeakPower(int $face): int {
        return $this->getOutputSignalStrength();
    }

    public function isPowerSource(): bool {
        return $this->getOutputSignalStrength() > 0;
    }

    private function canBeSupportedBy(Block $block): bool {
        return !$block->getSupportType(Facing::UP)->equals(SupportType::NONE());
    }
}