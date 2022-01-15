<?php

namespace tedo0627\redstonecircuit\block\entity;

use pocketmine\math\Vector3;
use pocketmine\world\World;
use tedo0627\redstonecircuit\block\inventory\DropperInventory;

class BlockEntityDropper extends BlockEntityDispenser {

    public function __construct(World $world, Vector3 $pos) {
        parent::__construct($world, $pos);
        $this->inventory = new DropperInventory($this->getPosition());
    }

    /**
     * @return DropperInventory
     */
    public function getInventory() {
        return $this->inventory;
    }

    /**
     * @return DropperInventory
     */
    public function getRealInventory() {
        return $this->inventory;
    }

    public function getDefaultName(): string {
        return "Dropper";
    }
}