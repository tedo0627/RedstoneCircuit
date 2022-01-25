<?php

namespace tedo0627\redstonecircuit\block\inventory;

use pocketmine\block\inventory\BlockInventory;
use pocketmine\block\inventory\BlockInventoryTrait;
use pocketmine\inventory\SimpleInventory;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\world\Position;

class CommandInventory extends SimpleInventory implements BlockInventory, IWindowType {
    use BlockInventoryTrait;

    public function __construct(Position $holder) {
        $this->holder = $holder;
        parent::__construct(0);
    }

    public function getWindowType(): int {
        return WindowTypes::COMMAND_BLOCK;
    }
}