<?php

namespace redstone\blocks;

use pocketmine\Player;

use pocketmine\block\Block;
use pocketmine\block\Solid;

use pocketmine\item\Item;

use pocketmine\math\Vector3;

class BlockRedstoneLamp extends Solid implements IRedstone {
    use RedstoneTrait;

    protected $id = self::REDSTONE_LAMP;
    
    public function __construct(int $meta = 0){
        $this->meta = $meta;
    }
    
    public function getName() : string {
        return "Redstone Lamp";
    }
    
    public function getHardness() : float {
        return 0.3;
    }

    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool {
        if ($this->isBlockPowered($blockReplace)) {
            $this->getLevel()->setBlock($blockReplace, new BlockRedstoneLampLit());
        } else {
            $this->getLevel()->setBlock($blockReplace, $this);
        }
        return true;
    }

    public function getDrops(Item $item) : array {
        return [Item::get(self::REDSTONE_LAMP, 0, 1)];
    }

    public function getStrongPower(int $face) : int {
        return 0;
    }

    public function getWeakPower(int $face) : int {
        return 0;
    }

    public function isPowerSource() : bool {
        return false;
    }

    public function onRedstoneUpdate() : void {
        if ($this->isBlockPowered($this)) {
            $this->getLevel()->setBlock($this, new BlockRedstoneLampLit());
        }
    }
}