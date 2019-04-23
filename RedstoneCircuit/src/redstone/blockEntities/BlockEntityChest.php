<?php

namespace redstone\blockEntities;

use pocketmine\math\Vector3;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;

use pocketmine\tile\Chest;


use redstone\blocks\BlockTrappedChest;

use redstone\inventories\ChestInventory;
use redstone\inventories\DoubleChestInventory;

class BlockEntityChest extends Chest {

    protected function readSaveData(CompoundTag $nbt) : void{
        parent::readSaveData($nbt);
        $this->inventory = new ChestInventory($this);
        $this->loadItems($nbt);
    }

    protected function checkPairing(){
        if($this->isPaired() and !$this->getLevel()->isInLoadedTerrain($this->getPair())){
            //paired to a tile in an unloaded chunk
            $this->doubleInventory = null;

        }elseif(($pair = $this->getPair()) instanceof Chest){
            if(!$pair->isPaired()){
                $pair->createPair($this);
                $pair->checkPairing();
            }
            if($this->doubleInventory === null){
                if($pair->doubleInventory !== null){
                    $this->doubleInventory = $pair->doubleInventory;
                }else{
                    if(($pair->x + ($pair->z << 15)) > ($this->x + ($this->z << 15))){ //Order them correctly
                        $this->doubleInventory = $pair->doubleInventory = new DoubleChestInventory($pair, $this);
                    }else{
                        $this->doubleInventory = $pair->doubleInventory = new DoubleChestInventory($this, $pair);
                    }
                }
            }
        }else{
            $this->doubleInventory = null;
            $this->pairX = $this->pairZ = null;
        }
    }

    public function onInventoryClose() : void {
        if ($this->level == null) {
            return;
        }

        $block = $this->getBlock();
        if ($block instanceof BlockTrappedChest) {
            $block->onInventoryClose();
        }
    }
}