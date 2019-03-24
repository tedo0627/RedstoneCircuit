<?php

namespace redstone\inventories;

use pocketmine\Player;

class ChestInventory extends \pocketmine\inventory\ChestInventory {

	public function onClose(Player $who) : void {
        parent::onClose($who);
        $this->getHolder()->onInventoryClose();
    }
}