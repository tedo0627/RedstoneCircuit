<?php

namespace redstone\inventories;

use pocketmine\Player;

class DoubleChestInventory extends \pocketmine\inventory\DoubleChestInventory {

    public function onClose(Player $who) : void {
        parent::onClose($who);
        $this->getHolder()->onInventoryClose();
    }
}