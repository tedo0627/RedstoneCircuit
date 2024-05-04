<?php

namespace tedo0627\redstonecircuit\block\mechanism;

use pocketmine\block\Block;
use pocketmine\block\Door;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\DoorSound;
use tedo0627\redstonecircuit\block\BlockPowerHelper;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\event\BlockRedstonePowerUpdateEvent;
use tedo0627\redstonecircuit\RedstoneCircuit;

class BlockIronDoor extends Door implements IRedstoneComponent {
    use RedstoneComponentTrait;

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool {
        $other = $this->getSide(Facing::UP);
        $powered = BlockPowerHelper::isPowered($this) || BlockPowerHelper::isPowered($other);
        $this->setPowered($powered);
        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null): bool {
        return false;
    }

    public function onRedstoneUpdate(): void {
        $other = $this->getSide($this->isTop() ? Facing::DOWN : Facing::UP);
        $powered = BlockPowerHelper::isPowered($this) || BlockPowerHelper::isPowered($other);
        if ($powered === $this->isPowered()) return;

        if (RedstoneCircuit::isCallEvent()) {
            $event = new BlockRedstonePowerUpdateEvent($this, $powered, $this->isPowered());
            $event->call();
            $powered = $event->getNewPowered();
            if ($powered === $this->isPowered()) return;
        }

        $this->setPowered($powered);
        $world = $this->getPosition()->getWorld();
        if ($this->isOpen() !== $powered) {
            $this->setOpen($powered);
            $world->addSound($this->getPosition(), new DoorSound());
        }
        $world->setBlock($this->getPosition(), $this);

        if ($other instanceof Door && $this->isSameType($other)) {
            $other->setPowered($this->isPowered());
            $other->setOpen($this->isOpen());
            $world->setBlock($other->getPosition(), $other);
        }
    }
}