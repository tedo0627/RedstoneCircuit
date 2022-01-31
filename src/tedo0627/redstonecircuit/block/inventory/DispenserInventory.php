<?php

namespace tedo0627\redstonecircuit\block\inventory;

use pocketmine\block\inventory\BlockInventory;
use pocketmine\block\inventory\BlockInventoryTrait;
use pocketmine\inventory\SimpleInventory;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\world\Position;

class DispenserInventory extends SimpleInventory implements BlockInventory, IWindowType {
    use BlockInventoryTrait;

    public function __construct(Position $holder) {
        $this->holder = $holder;
        parent::__construct(9);
    }

    public function getWindowType(): int {
        return WindowTypes::DISPENSER;
    }

    public function getRandomSlot(): int {
        $slots = [];
        for ($slot = 0; $slot < $this->getSize(); $slot++) {
            if (!$this->getItem($slot)->isNull()) $slots[] = $slot;
        }

        return count($slots) === 0 ? -1 : $slots[array_rand($slots)];
    }
}