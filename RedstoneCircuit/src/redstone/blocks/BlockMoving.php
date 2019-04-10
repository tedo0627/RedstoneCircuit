<?php

namespace redstone\blocks;

use pocketmine\Player;

use pocketmine\block\Block;
use pocketmine\block\Transparent;

use pocketmine\item\Item;

use pocketmine\math\Vector3;

use pocketmine\tile\Tile;


use redstone\blockEntities\BlockEntityMovingBlock;

class BlockMoving extends Transparent {

    protected $id = self::MOVINGBLOCK;
    
    public function __construct(int $meta = 0){
        $this->meta = $meta;
    }

    public function getName() : string {
        return "Movingblock";
    }

    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool {
        $this->getLevel()->setBlock($this, $this, true, true);
        Tile::createTile("BlockEntityMovingBlock", $this->getLevel(), BlockEntityMovingBlock::createNBT($this));
        return true;
    }
    
    public function getDrops(Item $item) : array {
        $tile = $this->getBlockEntity();
        return [$tile->getDrops($item)];
    }

    public function getBlockEntity() : BlockEntityMovingBlock {
        $tile = $this->getLevel()->getTile($this);
        $moving = null;
        if($tile instanceof BlockEntityMovingBlock){
            $moving = $tile;
        }else{
            $moving = Tile::createTile("BlockEntityMovingBlock", $this->getLevel(), BlockEntityMovingBlock::createNBT($this));
        }
        return $moving;
    }

    public function setData(Block $piston, Block $sourceBlock, ?Tile $tile) : void {
        $this->getBlockEntity()->setData($piston, $sourceBlock, $tile);
    }

    public function setMovedBlock() : void {
        $this->getBlockEntity()->setBlock();
    }
}