<?php

namespace tedo0627\redstonecircuit\block\power;

use pocketmine\block\Jukebox;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use tedo0627\redstonecircuit\block\BlockUpdateHelper;
use tedo0627\redstonecircuit\block\ILinkRedstoneWire;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\LinkRedstoneWireTrait;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\event\BlockRedstonePowerUpdateEvent;
use tedo0627\redstonecircuit\RedstoneCircuit;

class BlockJukeBox extends Jukebox implements IRedstoneComponent, ILinkRedstoneWire {
    use LinkRedstoneWireTrait;
    use RedstoneComponentTrait;

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null): bool {
        if (RedstoneCircuit::isCallEvent()) {
            $powered = $this->getRecord() !== null;
            $event = new BlockRedstonePowerUpdateEvent($this, !$powered, $powered);
            $event->call();
        }
        parent::onInteract($item, $face, $clickVector, $player);
        BlockUpdateHelper::updateAroundRedstone($this);
        return true;
    }

    public function getWeakPower(int $face): int {
        return $this->getRecord() !== null ? 15 : 0;
    }

    public function isPowerSource(): bool {
        return $this->getRecord() !== null;
    }
}