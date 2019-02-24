<?php

namespace redstone\inventories;

use pocketmine\inventory\ContainerInventory;

use pocketmine\network\mcpe\protocol\types\WindowTypes;


use redstone\blockEntities\BlockEntityDropper;

class DropperInventory extends ContainerInventory {

	public function __construct(BlockEntityDropper $tile){
		parent::__construct($tile);
	}

	public function getNetworkType() : int {
		return WindowTypes::DROPPER;
	}

	public function getName() : string {
		return "Dropper";
	}

	public function getDefaultSize() : int {
		return 9;
	}
}