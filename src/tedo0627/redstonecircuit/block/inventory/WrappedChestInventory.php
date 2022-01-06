<?php

namespace tedo0627\redstonecircuit\block\inventory;

use pocketmine\block\inventory\ChestInventory;
use pocketmine\player\Player;

class WrappedChestInventory extends ChestInventory {

    public function onOpen(Player $who): void {
        parent::onOpen($who);
        $pos = $this->getHolder();
        $pos->getWorld()->scheduleDelayedBlockUpdate($pos, 1);
    }

    public function onClose(Player $who): void {
        parent::onClose($who);
        $pos = $this->getHolder();
        $pos->getWorld()->scheduleDelayedBlockUpdate($pos, 1);
    }
}