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

use redstone\blockEntities\BlockEntityDropper;

class BlockDropper extends Solid implements IRedstone {
    use RedstoneTrait;

    protected $id = self::DROPPER;
    
    public function __construct(int $meta = 0){
        $this->meta = $meta;
    }

    public function getName() : string {
        return "Dropper";
    }
    
    public function getHardness() : float {
        return 3.5;
    }

    public function getToolType() : int{
        return BlockToolType::TYPE_PICKAXE;
    }

    public function getToolHarvestLevel() : int{
        return TieredTool::TIER_WOODEN;
    }
    
    public function getVariantBitmask() : int {
        return 0;
    }

    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool {
        $damage = 0;
        if($player !== null) {
            $faces = [4, 2, 5, 3];
            $damage = $faces[$player->getDirection()];
            if ($player->getPitch() > 45) {
                $damage = 1;
            } else if ($player->getPitch() < -45) {
                $damage = 0;
            }
        }

        $this->setDamage($damage);
        $this->level->setBlock($this, $this, true, true);
        
        Tile::createTile("Dropper", $this->getLevel(), BlockEntityDropper::createNBT($this->asVector3()));
        return true;
    }

    public function onActivate(Item $item, Player $player = null) : bool{
        if($player instanceof Player){
            $tile = $this->getLevel()->getTile($this);
            $dropper = null;
            if($tile instanceof BlockEntityDropper){
                $dropper = $tile;
            }else{
                $dropper = Tile::createTile("BlockEntityDropper", $this->getLevel(), BlockEntityDropper::createNBT($this));
            }

            $player->addWindow($dropper->getInventory());
        }
        return true;
    }
    
    public function onScheduledUpdate() : void {
        $dropper = $this->getBlockEntity();
        $dropper->dropItem();
    }
    
    public function getBlockEntity() : BlockEntityDropper {
        $tile = $this->getLevel()->getTile($this);
        $dropper = null;
        if($tile instanceof BlockEntityDropper){
            $dropper = $tile;
        }else{
            $dropper = Tile::createTile("BlockEntityDropper", $this->getLevel(), BlockEntityDropper::createNBT($this));
        }
        return $dropper;
    }

    public function isActivated() : bool {
        return $this->getDamage() >= 8;
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
        if ($this->isActivated()) {
            if ($this->isBlockPowered($this->asVector3())) {
                return;
            }
            $this->setDamage($this->getDamage() - 8);
            $this->level->setBlock($this, $this);
        } else {
            if (!$this->isBlockPowered($this->asVector3())) {
                return;
            }
            $this->setDamage($this->getDamage() + 8);
            $this->level->setBlock($this, $this);
            $this->level->scheduleDelayedBlockUpdate($this, 4);
        }

    }
}
