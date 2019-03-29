<?php

namespace redstone\blocks;

use pocketmine\Player;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\Transparent;

use pocketmine\item\Item;


use redstone\blocks\BlockPiston;

use redstone\utils\Facing;

class BlockPistonarmcollision extends Transparent {

    protected $id = self::PISTONARMCOLLISION;
    
    public function __construct(int $meta = 0){
        $this->meta = $meta;
    }

    public function getName() : string {
        return "pistonarmcollision";
    }
 
    public function onBreak(Item $item, Player $player = null) : bool {
        $this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR));
        $block = $this->getSide($this->getDamage());
        if ($block instanceof BlockPiston) {
            $this->getLevel()->useBreakOn($block);
        }
        return true;
    }

    public function onNearbyBlockChange() : void {
        $block = $this->getSide($this->getDamage());
        if (!($block instanceof BlockPiston)) {
            $this->getLevel()->useBreakOn($this);
        }
    }

    public function getDrops(Item $item) : array {
        return [];
    }
}