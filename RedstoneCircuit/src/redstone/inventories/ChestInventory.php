<?php

namespace redstone\inventories;

use pocketmine\Player;
use pocketmine\inventory\ChestInventory as PMChestInventory;

class ChestInventory extends PMChestInventory {

	public function onClose(Player $who) : void {
        parent::onClose($who);
        $this->getHolder()->onInventoryClose();// HACK
    }
}