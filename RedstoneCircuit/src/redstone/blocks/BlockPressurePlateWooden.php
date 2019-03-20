<?php

namespace redstone\blocks;

use pocketmine\block\BlockToolType;

use pocketmine\item\TieredTool;

class BlockPressurePlateWooden extends BlockPressurePlateBase {

    protected $id = self::WOODEN_PRESSURE_PLATE;
    
    public function getName() : string {
        return "Wooden Pressure Plate";
    }

    public function getToolType() : int {
        return BlockToolType::TYPE_AXE;
    }

    public function getToolHarvestLevel() : int {
        return TieredTool::TIER_WOODEN;
    }

    public function computeDamage() : int {
        $count = count($this->level->getNearbyEntities($this->bb()));
        return $count > 0 ? 1 : 0;
    }

    public function getDelay() : int {
        return 20;
    }

    public function getOnSoundExtraData() : int {
        return 2583;
    }

    public function getOffSoundExtraData() : int {
        return 257;
    }
}