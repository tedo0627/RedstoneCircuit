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
use tedo0627\redstonecircuit\event\BlockRedstonePowerUpdateEvent;
use tedo0627\redstonecircuit\RedstoneCircuit;

class BlockIronTrapdoor extends Trapdoor implements IRedstoneComponent {
    use RedstoneComponentTrait;

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null): bool {
        return false;
    }

    public function onRedstoneUpdate(): void {
        $powered = BlockPowerHelper::isPowered($this);
        if ($powered === $this->isOpen()) return;

        if (RedstoneCircuit::isCallEvent()) {
            $event = new BlockRedstonePowerUpdateEvent($this, $powered, $this->isOpen());
            $event->call();
            $powered = $event->getNewPowered();
            if ($powered === $this->isOpen()) return;
        }

        $this->setOpen($powered);
        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
        $this->getPosition()->getWorld()->addSound($this->getPosition(), new DoorSound());
    }
}