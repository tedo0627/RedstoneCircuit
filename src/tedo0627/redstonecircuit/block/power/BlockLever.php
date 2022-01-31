<?php

namespace tedo0627\redstonecircuit\block\power;

use pocketmine\block\Block;
use pocketmine\block\Lever;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use tedo0627\redstonecircuit\block\BlockUpdateHelper;
use tedo0627\redstonecircuit\block\FlowablePlaceHelper;
use tedo0627\redstonecircuit\block\ILinkRedstoneWire;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\LinkRedstoneWireTrait;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;

class BlockLever extends Lever implements IRedstoneComponent, ILinkRedstoneWire {
    use LinkRedstoneWireTrait;
    use RedstoneComponentTrait;

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool {
        if (!FlowablePlaceHelper::checkSurface($this, Facing::opposite($face))) return false;
        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

    public function onBreak(Item $item, ?Player $player = null): bool {
        parent::onBreak($item, $player);
        BlockUpdateHelper::updateAroundDirectionRedstone($this, Facing::opposite($this->getFacing()->getFacing()));
        return true;
    }

    public function onNearbyBlockChange(): void {
        if (FlowablePlaceHelper::checkSurface($this, Facing::opposite($this->getFacing()->getFacing()))) return;
        $this->getPosition()->getWorld()->useBreakOn($this->getPosition());
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null): bool {
        parent::onInteract($item, $face, $clickVector, $player);
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