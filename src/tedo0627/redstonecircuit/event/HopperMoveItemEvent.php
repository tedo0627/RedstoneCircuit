<?php

namespace tedo0627\redstonecircuit\event;

use pocketmine\block\Hopper;
use pocketmine\block\Jukebox;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;

class HopperMoveItemEvent extends BlockEvent implements Cancellable {
    use CancellableTrait;

    private Hopper $hopper;
    private Inventory $source;
    private ?Inventory $targetInventory = null;
    private ?Jukebox $targetJukebox = null;
    private Item $item;

    public function __construct(Hopper $hopper, Inventory $source, Inventory|Jukebox $target, Item $item) {
        parent::__construct($hopper);

        $this->hopper = $hopper;
        $this->source = $source;
        if ($target instanceof Inventory) {
            $this->targetInventory = $target;
        } else {
            $this->targetJukebox = $target;
        }
        $this->item = $item;
    }

    public function getHopper(): Hopper {
        return $this->hopper;
    }

    public function getSourceInventory(): Inventory {
        return $this->source;
    }

    public function getTargetInventory(): ?Inventory {
        return $this->targetInventory;
    }

    public function getJukebox(): ?Jukebox {
        return $this->targetJukebox;
    }

    public function getItem(): Item {
        return $this->item;
    }
}