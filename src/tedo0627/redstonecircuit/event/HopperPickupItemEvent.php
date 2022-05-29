<?php

namespace tedo0627\redstonecircuit\event;

use pocketmine\block\Hopper;
use pocketmine\entity\object\ItemEntity;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;

class HopperPickupItemEvent extends BlockEvent implements Cancellable {
    use CancellableTrait;

    private Hopper $hopper;
    private Inventory $inventory;
    private ItemEntity $entity;
    private Item $item;

    public function __construct(Hopper $hopper, Inventory $inventory, ItemEntity $entity, Item $item) {
        parent::__construct($hopper);

        $this->hopper = $hopper;
        $this->inventory = $inventory;
        $this->entity = $entity;
        $this->item = $item;
    }

    public function getHopper(): Hopper {
        return $this->hopper;
    }

    public function getInventory(): Inventory {
        return $this->inventory;
    }

    public function getItemEntity(): ItemEntity {
        return $this->entity;
    }

    public function getItem(): Item {
        return $this->item;
    }
}