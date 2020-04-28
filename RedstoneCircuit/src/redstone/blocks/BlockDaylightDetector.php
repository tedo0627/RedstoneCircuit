<?php

namespace redstone\blocks;

use pocketmine\Player;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\DaylightSensor;

use pocketmine\item\Item;

use pocketmine\math\Vector3;

use pocketmine\tile\Tile;

use redstone\blockEntities\BlockEntityDaylightDetector;

class BlockDaylightDetector extends DaylightSensor implements IRedstone {
    use RedstoneTrait;
    
    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool {
        $this->getLevel()->setBlock($this, $this, true, true);
        Tile::createTile("BlockEntityDaylightDetector", $this->getLevel(), BlockEntityDaylightDetector::createNBT($this));
        return true;
    }

    public function onActivate(Item $item, Player $player = null) : bool {
        $this->getLevel()->setBlock($this, new BlockDaylightDetectorInverted(), true, true);
        $this->getLevel()->getBlock($this)->updatePower();
        return true;
    }
    
    public function onBreak(Item $item, Player $player = null) : bool {
        $this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR));
        $this->updateAroundRedstone($this);
        return true;
    }
    
    public function getBlockEntity() : BlockEntityDaylightDetector {
        $tile = $this->getLevel()->getTile($this);
        $detector = null;
        if($tile instanceof BlockEntityDaylightDetector){
            $detector = $tile;
        }else{
            $detector = Tile::createTile("BlockEntityDaylightDetector", $this->getLevel(), BlockEntityDaylightDetector::createNBT($this));
        }
        return $detector;
    }

    public function updatePower() : void {
        $power = $this->getBlockEntity()->getPower();
        $this->setDamage($power);
        $this->getLevel()->setBlock($this, $this);

        $this->updateAroundRedstone($this);
    }
    
    public function getStrongPower(int $face) : int {
        return 0;
    }

    public function getWeakPower(int $face) : int {
        return $this->getDamage();
    }

    public function isPowerSource() : bool {
        return $this->getDamage() > 0;
    }

    public function onRedstoneUpdate() : void {
    }
}