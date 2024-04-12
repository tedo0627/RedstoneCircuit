<?php

namespace tedo0627\redstonecircuit\block\power;

use pocketmine\block\Lever;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use tedo0627\redstonecircuit\block\BlockUpdateHelper;
use tedo0627\redstonecircuit\block\ILinkRedstoneWire;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\LinkRedstoneWireTrait;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\CustomConfig;
use tedo0627\redstonecircuit\event\BlockRedstonePowerUpdateEvent;

class BlockLever extends Lever implements IRedstoneComponent, ILinkRedstoneWire {
    use LinkRedstoneWireTrait;
    use RedstoneComponentTrait;

    public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []): bool {
        parent::onBreak($item, $player, $returnedItems);
        BlockUpdateHelper::updateAroundDirectionRedstone($this, Facing::opposite($this->getFacing()->getFacing()));
        return true;
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []): bool {
        if (CustomConfig::isCallEvent()) {
            $powered = $this->isActivated();
            $event = new BlockRedstonePowerUpdateEvent($this, !$powered, $powered);
            $event->call();
        }
        parent::onInteract($item, $face, $clickVector, $player, $returnedItems);
        BlockUpdateHelper::updateAroundDirectionRedstone($this, Facing::opposite($this->getFacing()->getFacing()));
        return true;
    }

    public function getStrongPower(int $face): int {
        if (!$this->isActivated()) return 0;
        return $face === $this->getFacing()->getFacing() ? 15 : 0;
    }

    public function getWeakPower(int $face): int {
        return $this->isActivated() ? 15 : 0;
    }

    public function isPowerSource(): bool {
        return $this->isActivated();
    }
}