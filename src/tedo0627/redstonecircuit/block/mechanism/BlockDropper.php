<?php

namespace tedo0627\redstonecircuit\block\mechanism;

use pocketmine\block\Block;
use pocketmine\block\Opaque;
use pocketmine\block\utils\AnyFacingTrait;
use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\block\utils\PoweredByRedstoneTrait;
use pocketmine\item\Item;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\ClickSound;
use tedo0627\redstonecircuit\block\BlockPowerHelper;
use tedo0627\redstonecircuit\block\entity\BlockEntityDropper;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\sound\ClickFailSound;

class BlockDropper extends Opaque implements IRedstoneComponent {
    use AnyFacingTrait;
    use PoweredByRedstoneTrait;
    use RedstoneComponentTrait;

    protected function writeStateToMeta(): int {
        return BlockDataSerializer::writeFacing($this->facing) |
            ($this->isPowered() ? 0x08 : 0);
    }

    public function readStateFromData(int $id, int $stateMeta): void {
        $this->setFacing(BlockDataSerializer::readFacing($stateMeta & 0x07));
        $this->setPowered(($stateMeta & 0x08) !== 0);
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
                $this->setFacing(Facing::UP);
            } elseif ($y < -1 && $x < 2 && $z < 2) {
                $this->setFacing(Facing::DOWN);
            } else {
                $this->setFacing(Facing::opposite($player->getHorizontalFacing()));
            }
        }
        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null): bool {
        if ($player === null) return true;

        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        if (!$tile instanceof BlockEntityDropper) return true;

        $inventory = $tile->getInventory();
        $player->setCurrentWindow($inventory);
        return true;
    }

    public function onRedstoneUpdate(): void {
        $powered = BlockPowerHelper::isPowered($this);
        if ($powered && !$this->isPowered()) {
            $this->setPowered(true);
            $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);

            $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
            if (!$tile instanceof BlockEntityDropper) return;

            $inventory = $tile->getInventory();
            $slot = $inventory->getRandomSlot();
            if ($slot === -1) {
                $this->getPosition()->getWorld()->addSound($this->getPosition(), new ClickFailSound(1.2));
                return;
            }

            $item = $inventory->getItem($slot);
            $drop = $item->pop();
            $inventory->setItem($slot, $item);
            $face = $this->getFacing();
            $facePos = Vector3::zero()->getSide($face)->multiply(0.6);
            $pos = $this->getPosition()->add(0.5, 0.5, 0.5)->addVector($facePos);
            $v = mt_rand(0, 100) / 1000 + 0.2;
            $motion = new Vector3(
                mt_rand(-100, 100) / 100 * 0.0075 * 6 + (Facing::axis($face) == Axis::X ? 1.0 : 0.0) * $v * (Facing::isPositive($face) ? 1.0 : -1.0),
                mt_rand(-100, 100) / 100 * 0.0075 * 6 + 0.2,
                mt_rand(-100, 100) / 100 * 0.0075 * 6 + (Facing::axis($face) == Axis::Z ? 1.0 : 0.0) * $v * (Facing::isPositive($face) ? 1.0 : -1.0),
            );
            $this->getPosition()->getWorld()->dropItem($pos, $drop, $motion);
            $this->getPosition()->getWorld()->addSound($this->getPosition(), new ClickSound());
            return;
        }

        if ($powered || !$this->isPowered()) return;

        $this->setPowered(false);
        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
    }
}