<?php

namespace redstone\inventories;

use pocketmine\inventory\ContainerInventory;

use pocketmine\network\mcpe\protocol\types\WindowTypes;


use redstone\blockEntities\BlockEntityCommandBlock;

class CommandInventory extends ContainerInventory {

    public function __construct(BlockEntityCommandBlock $tile){
        parent::__construct($tile);
    }

    public function getNetworkType() : int {
        return WindowTypes::COMMAND_BLOCK;
    }

    public function getName() : string {
        return "Command Block";
    }

    public function getDefaultSize() : int {
        return 0;
    }
}