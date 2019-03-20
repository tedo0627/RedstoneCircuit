<?php

namespace redstone\blocks;

use pocketmine\block\BlockToolType;

use pocketmine\entity\Living;

use pocketmine\item\TieredTool;

class BlockPressurePlateStone extends BlockPressurePlateBase {

    protected $id = self::STONE_PRESSURE_PLATE;
    
    public function getName() : string {
        return "Stone Pressure Plate";
    }

    public function getToolType() : int {
        return BlockToolType::TYPE_PICKAXE;
    }

    public function getToolHarvestLevel() : int {
        return TieredTool::TIER_WOODEN;
    }

    public function computeDamage() : int {
        $count = 0;
        $entities = $this->level->getNearbyEntities($this->bb());
        for ($i = 0; $i < count($entities); ++$i) {
            if ($entities[$i] instanceof Living) {
                $count++;
            }
        }
        
        return $count > 0 ? 1 : 0;
    }

    public function getDelay() : int {
        return 20;
    }

    public function getOnSoundExtraData() : int {
        return 1591;
    }

    public function getOffSoundExtraData() : int {
        return 3542;
    }
}