<?php

namespace redstone\blocks;

use pocketmine\Player;

use pocketmine\block\Block;
use pocketmine\block\BlockToolType;
use pocketmine\block\Solid;

use pocketmine\item\Item;
use pocketmine\item\TieredTool;

use pocketmine\math\Vector3;

use pocketmine\tile\Tile;

use redstone\blockEntities\BlockEntityHopper;

use redstone\utils\Facing;

class BlockHopper extends Solid implements IRedstone {
    use RedstoneTrait;

    protected $id = self::HOPPER_BLOCK;
    protected $itemId = Item::HOPPER;

    public function __construct(int $meta = 0){
        $this->meta = $meta;
    }

    public function getName() : string {
        return "Hopper";
    }
    
    public function getVariantBitmask() : int {
        return 0;
    }

    public function isTransparent() : bool{
        return true;
    }

    public function getLightFilter() : int{
        return 0;
    }

    public function getToolType() : int {
        return BlockToolType::TYPE_PICKAXE;
    }

    public function getToolHarvestLevel() : int {
        return TieredTool::TIER_WOODEN;
    }
    
    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool {
        $face = Facing::opposite($face);
        if ($face == Facing::UP) {
            $face = Facing::DOWN;
        }

        $this->setDamage($face);
        $this->level->setBlock($this, $this);

        $nbt = BlockEntityHopper::createNBT($this);
        if ($item->hasCustomName()) {
            $nbt->setString("CustomName", $item->getCustomName());
        }

        Tile::createTile("BlockEntityHopper", $this->getLevel(), $nbt);
        return true;
    }

    public function onActivate(Item $item, Player $player = null) : bool{
        $hopper = $this->getBlockEntity();
        $inventory = $hopper->getInventory();
        $player->addWindow($inventory);
        return true;
    }
    
    public function getBlockEntity() : BlockEntityHopper {
        $tile = $this->getLevel()->getTile($this);
        $hopper = null;
        if($tile instanceof BlockEntityHopper){
            $hopper = $tile;
        }else{
            $hopper = Tile::createTile("BlockEntityHopper", $this->getLevel(), BlockEntityHopper::createNBT($this));
        }
        return $hopper;
    }

    public function isRedstoneLocked() : bool {
        return $this->getDamage() >= 8;
    }

    public function getFace() : int {
        return $this->getDamage();
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
        if ($this->isBlockPowered($this->asVector3()) && $this->getDamage() < 8) {
            $this->setDamage($this->getDamage() + 8);
            $this->getLevel()->setBlock($this, $this);
        } else if (!$this->isBlockPowered($this->asVector3()) && $this->getDamage() >= 8) {
            $this->setDamage($this->getDamage() - 8);
            $this->getLevel()->setBlock($this, $this);
        }
    }
}