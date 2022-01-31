<?php

namespace tedo0627\redstonecircuit\block\inventory;

use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;

class DropperInventory extends DispenserInventory {

    public function getWindowType(): int {
        return WindowTypes::DROPPER;
    }
}