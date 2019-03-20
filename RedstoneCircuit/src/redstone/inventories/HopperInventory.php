<?php

namespace redstone\inventories;

use pocketmine\inventory\ContainerInventory;

use pocketmine\network\mcpe\protocol\types\WindowTypes;


use redstone\blockEntities\BlockEntityHopper;

class HopperInventory extends ContainerInventory {

    public function __construct(BlockEntityHopper $tile){
        parent::__construct($tile);
    }

    public function getNetworkType() : int {
        return WindowTypes::HOPPER;
    }

    public function getName() : string {
        return "Hopper";
    }

    public function getDefaultSize() : int {
        return 5;
    }
}