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

use redstone\blockEntities\BlockEntityDispenser;

class BlockDispenser extends Solid implements IRedstone {
    use RedstoneTrait;

    protected $id = self::DISPENSER;
    
    public function __construct(int $meta = 0){
        $this->meta = $meta;
    }

    public function getName() : string {
        return "Dispenser";
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

        Tile::createTile("BlockEntityDispenser", $this->getLevel(), BlockEntityDispenser::createNBT($this));
        return true;
    }

    public function onActivate(Item $item, Player $player = null) : bool{
        if($player instanceof Player){
            $tile = $this->getLevel()->getTile($this);
            $dropdispenserper = null;
            if($tile instanceof BlockEntityDispenser){
                $dispenser = $tile;
            }else{
                $dispenser = Tile::createTile("BlockEntityDispenser", $this->getLevel(), BlockEntityDispenser::createNBT($this));
            }

            $player->addWindow($dispenser->getInventory());
        }
        return true;
    }
    
    public function onScheduledUpdate() : void {
        $dispenser = $this->getBlockEntity();
        $dispenser->dropItem();
    }
    
    public function getBlockEntity() : BlockEntityDispenser {
        $tile = $this->getLevel()->getTile($this);
        $dispenser = null;
        if($tile instanceof BlockEntityDispenser){
            $dispenser = $tile;
        }else{
            $dispenser = Tile::createTile("BlockEntityDispenser", $this->getLevel(), BlockEntityDispenser::createNBT($this));
        }
        return $dispenser;
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