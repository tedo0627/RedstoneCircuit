<?php

namespace redstone\inventories;

use pocketmine\inventory\ContainerInventory;

use pocketmine\network\mcpe\protocol\types\WindowTypes;


use redstone\blockEntities\BlockEntityDispenser;

class DispenserInventory extends ContainerInventory {

    public function __construct(BlockEntityDispenser $tile){
        parent::__construct($tile);
    }

    public function getNetworkType() : int {
        return WindowTypes::DISPENSER;
    }

    public function getName() : string {
        return "Dispenser";
    }

    public function getDefaultSize() : int {
        return 9;
    }
}