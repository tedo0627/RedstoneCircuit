<?php

namespace tedo0627\redstonecircuit\block\power;

use pocketmine\block\Block;
use pocketmine\block\WoodenPressurePlate;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\RedstonePowerOffSound;
use pocketmine\world\sound\RedstonePowerOnSound;
use tedo0627\redstonecircuit\block\BlockUpdateHelper;
use tedo0627\redstonecircuit\block\FlowablePlaceHelper;
use tedo0627\redstonecircuit\block\ILinkRedstoneWire;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\LinkRedstoneWireTrait;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;

class BlockWoodenPressurePlate extends WoodenPressurePlate implements IRedstoneComponent, ILinkRedstoneWire {
    use LinkRedstoneWireTrait;
    use RedstoneComponentTrait;

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool {
        if (!FlowablePlaceHelper::check($this, Facing::DOWN)) return false;
        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

    public function onBreak(Item $item, ?Player $player = null): bool {
        parent::onBreak($item, $player);
        BlockUpdateHelper::updateAroundDirectionRedstone($this, Facing::DOWN);
        return true;
    }

    public function onNearbyBlockChange(): void {
        if (FlowablePlaceHelper::check($this, Facing::DOWN)) return;
        $this->getPosition()->getWorld()->useBreakOn($this->getPosition());
    }

    public function onScheduledUpdate(): void {
        if (!$this->isPressed()) return;

        $entities = $this->getPosition()->getWorld()->getNearbyEntities($this->getHitCollision());
        if (count($entities) !== 0) {
            $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 20);
            return;
        }

        $this->setPressed(false);
        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
        $this->getPosition()->getWorld()->addSound($this->getPosition()->add(0.5, 0.5, 0.5), new RedstonePowerOffSound());
        BlockUpdateHelper::updateAroundDirectionRedstone($this, Facing::DOWN);
    }

    public function onEntityInside(Entity $entity): bool {
        $entities = $this->getPosition()->getWorld()->getNearbyEntities($this->getHitCollision());
        if (count($entities) <= 0) return true;

        if (!$this->isPressed()) {
            $this->setPressed(true);
            $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
            $this->getPosition()->getWorld()->addSound($this->getPosition()->add(0.5, 0.5, 0.5), new RedstonePowerOnSound());
            BlockUpdateHelper::updateAroundDirectionRedstone($this, Facing::DOWN);
        }
        $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 20);
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
        return $this->isPressed() && $face == Facing::UP ? 15 : 0;
    }

    public function getWeakPower(int $face): int {
        return $this->isPressed() ? 15 : 0;
    }

    public function isPowerSource(): bool {
        return $this->isPressed();
    }
}