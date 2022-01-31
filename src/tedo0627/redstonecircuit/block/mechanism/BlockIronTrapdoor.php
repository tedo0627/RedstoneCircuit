<?php

namespace tedo0627\redstonecircuit\block\mechanism;

use pocketmine\block\Trapdoor;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\sound\DoorSound;
use tedo0627\redstonecircuit\block\BlockPowerHelper;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;

class BlockIronTrapdoor extends Trapdoor implements IRedstoneComponent {
    use RedstoneComponentTrait;

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null): bool {
        return false;
    }

    public function onRedstoneUpdate(): void {
        $powered = BlockPowerHelper::isPowered($this);
        $world = $this->getPosition()->getWorld();
        if ($powered && !$this->isOpen()) {
            $this->setOpen(true);
            $world->setBlock($this->getPosition(), $this);
            $world->addSound($this->getPosition(), new DoorSound());
            return;
        }

        if ($powered || !$this->isOpen()) return;

        $this->setOpen(false);
        $world->setBlock($this->getPosition(), $this);
        $world->addSound($this->getPosition(), new DoorSound());
    }
}