<?php

namespace redstone\blocks;

use pocketmine\Player;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockToolType;
use pocketmine\block\Solid;

use pocketmine\item\Item;
use pocketmine\item\TieredTool;

use pocketmine\math\Vector3;

class BlockRedstone extends Solid implements IRedstone {
    use RedstoneTrait;

    protected $id = self::REDSTONE_BLOCK;
    
    public function __construct(int $meta = 0){
        $this->meta = $meta;
    }

    public function getName() : string {
        return "Redstone Block";
    }
    
    public function getHardness() : float {
        return 5;
    }

    public function getToolType() : int{
        return BlockToolType::TYPE_PICKAXE;
    }

    public function getToolHarvestLevel() : int{
        return TieredTool::TIER_WOODEN;
    }

    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool {
        $this->getLevel()->setBlock($blockReplace, $this);
        $this->updateAroundRedstone($this);
        return true;
    }

    public function onBreak(Item $item, Player $player = null) : bool {
        $this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR));
        $this->updateAroundRedstone($this);
        return true;
    }

    public function getStrongPower(int $face) : int {
        return 0;
    }

    public function getWeakPower(int $face) : int {
        return 15;
    }

    public function isPowerSource() : bool {
        return true;
    }

    public function onRedstoneUpdate() : void {
    }
}