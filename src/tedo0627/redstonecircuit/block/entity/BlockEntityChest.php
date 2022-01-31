<?php

namespace tedo0627\redstonecircuit\block\entity;

use pocketmine\block\inventory\DoubleChestInventory;
use pocketmine\block\tile\Chest;
use pocketmine\math\Vector3;
use pocketmine\world\World;
use tedo0627\redstonecircuit\block\inventory\WrappedChestInventory;
use tedo0627\redstonecircuit\block\inventory\WrappedDoubleChestInventory;

class BlockEntityChest extends Chest {

    public function __construct(World $world, Vector3 $pos) {
        parent::__construct($world, $pos);
        $this->inventory = new WrappedChestInventory($this->getPosition());
    }

    protected function checkPairing(): void {
        parent::checkPairing();
        $pair = $this->getPair();
        if ($pair === null) return;

        $inventory = $this->doubleInventory;
        if (!$inventory instanceof DoubleChestInventory || $inventory instanceof WrappedDoubleChestInventory) return;

        $inventory = $pair->doubleInventory;
        if (!$inventory instanceof DoubleChestInventory || $inventory instanceof WrappedDoubleChestInventory) return;

        $inventory = new WrappedDoubleChestInventory($inventory->getLeftSide(), $inventory->getRightSide());
        $this->doubleInventory = $inventory;
        $pair->doubleInventory = $inventory;
    }
}