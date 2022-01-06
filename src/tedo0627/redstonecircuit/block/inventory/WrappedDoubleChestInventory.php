<?php

namespace tedo0627\redstonecircuit\block\inventory;

use pocketmine\block\inventory\DoubleChestInventory;
use pocketmine\player\Player;

class WrappedDoubleChestInventory extends DoubleChestInventory {

    public function onOpen(Player $who): void {
        parent::onOpen($who);
        $pos = $this->getLeftSide()->getHolder();
        $pos->getWorld()->scheduleDelayedBlockUpdate($pos, 1);
        $pos = $this->getRightSide()->getHolder();
        $pos->getWorld()->scheduleDelayedBlockUpdate($pos, 1);
    }

    public function onClose(Player $who): void {
        parent::onClose($who);

        $pos = $this->getLeftSide()->getHolder();
        $pos->getWorld()->scheduleDelayedBlockUpdate($pos, 1);
        $pos = $this->getRightSide()->getHolder();
        $pos->getWorld()->scheduleDelayedBlockUpdate($pos, 1);
    }
}