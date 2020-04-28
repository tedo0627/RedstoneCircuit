<?php

namespace redstone\blocks;

use pocketmine\Player;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\TrappedChest;

use pocketmine\item\Item;

use pocketmine\tile\Tile;
use pocketmine\tile\Chest;

use redstone\utils\Facing;

use function count;

class BlockTrappedChest extends TrappedChest implements IRedstone {
    use RedstoneTrait;

	public function onActivate(Item $item, Player $player = null) : bool {
        parent::onActivate($item, $player);
        $this->updateAroundRedstone($this);
        $this->updateAroundRedstone($this->asVector3()->getSide(Facing::DOWN));
		return true;
    }
    
    public function onBreak(Item $item, Player $player = null) : bool {
        $this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR));
        $this->updateAroundRedstone($this);
        $this->updateAroundRedstone($this->asVector3()->getSide(Facing::DOWN));
        return true;
    }
    
    public function onInventoryClose() : void {
        $this->updateAroundRedstone($this);
        $this->updateAroundRedstone($this->asVector3()->getSide(Facing::DOWN));
    }
    
    public function getBlockEntity() : Chest {
        $tile = $this->getLevel()->getTile($this);
        $chest = null;
        if($tile instanceof Chest){
            $chest = $tile;
        }else{
            $chest = Tile::createTile(Tile::CHEST, $this->getLevel(), TileChest::createNBT($this));
        }
        return $chest;
    }
    
    public function getStrongPower(int $face) : int {
        if ($face != Facing::UP) {
            return 0;
        }
        return $this->getWeakPower($face);
    }

    public function getWeakPower(int $face) : int {
        $count = count($this->getBlockEntity()->getInventory()->getViewers());
        if ($count > 15) {
            $count = 15;
        }
        return $count;
    }

    public function isPowerSource() : bool {
        return count($this->getBlockEntity()->getInventory()->getViewers()) > 0;
    }

    public function onRedstoneUpdate() : void {
    }
}