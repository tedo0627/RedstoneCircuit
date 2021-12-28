<?php

namespace tedo0627\redstonecircuit\block\power;

use pocketmine\block\Block;
use pocketmine\block\Redstone;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use tedo0627\redstonecircuit\block\BlockUpdateHelper;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;

class BlockRedstone extends Redstone implements IRedstoneComponent {
    use RedstoneComponentTrait;

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool {
        $bool = parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
        BlockUpdateHelper::updateAroundRedstone($this);
        return $bool;
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