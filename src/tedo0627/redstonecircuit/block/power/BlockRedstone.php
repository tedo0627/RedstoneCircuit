<?php

namespace tedo0627\redstonecircuit\block\power;

use pocketmine\block\Redstone;
use pocketmine\item\Item;
use pocketmine\player\Player;
use tedo0627\redstonecircuit\block\BlockUpdateHelper;
use tedo0627\redstonecircuit\block\ILinkRedstoneWire;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\LinkRedstoneWireTrait;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;

class BlockRedstone extends Redstone implements IRedstoneComponent, ILinkRedstoneWire {
    use LinkRedstoneWireTrait;
    use RedstoneComponentTrait;

    public function onPostPlace(): void {
        BlockUpdateHelper::updateAroundRedstone($this);
    }

    public function onBreak(Item $item, ?Player $player = null): bool {
        $bool = parent::onBreak($item, $player);
        BlockUpdateHelper::updateAroundRedstone($this);
        return $bool;
    }

    public function getWeakPower(int $face): int {
        return 15;
    }

    public function isPowerSource(): bool {
        return true;
    }
}