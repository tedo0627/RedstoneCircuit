<?php

namespace tedo0627\redstonecircuit\block\mechanism;

use pocketmine\block\TNT;
use tedo0627\redstonecircuit\block\BlockPowerHelper;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;

class BlockTNT extends TNT implements IRedstoneComponent {
    use RedstoneComponentTrait;

    public function onRedstoneUpdate(): void {
        if (BlockPowerHelper::isPowered($this)) $this->ignite();
    }
}