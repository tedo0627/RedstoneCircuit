<?php

namespace redstone\blocks;

use pocketmine\block\BlockToolType;

class BlockButtonStone extends BlockButtonBase {
    
    protected $id = self::STONE_BUTTON;

    public function getName() : string {
        return "Stone Button";
    }
    
    public function getHardness() : float{
        return 0.5;
    }

    public function getToolType() : int{
        return BlockToolType::TYPE_PICKAXE;
    }
    
    public function getActivateTime() : int {
        return 20;
    }
}