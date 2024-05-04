<?php

namespace tedo0627\redstonecircuit\block\mechanism;

use pocketmine\block\tile\Container;
use pocketmine\item\Item;
use pocketmine\world\sound\ClickSound;
use tedo0627\redstonecircuit\block\entity\BlockEntityDispenser;
use tedo0627\redstonecircuit\event\BlockDispenseEvent;
use tedo0627\redstonecircuit\RedstoneCircuit;
use tedo0627\redstonecircuit\sound\ClickFailSound;

class BlockDropper extends BlockDispenser {

    public function onScheduledUpdate(): void {
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        if (!$tile instanceof BlockEntityDispenser) return;

        $inventory = $tile->getInventory();
        $slot = $inventory->getRandomSlot();
        if ($slot === -1) {
            $this->getPosition()->getWorld()->addSound($this->getPosition(), new ClickFailSound(1.2));
            return;
        }

        $item = $inventory->getItem($slot);
        if (RedstoneCircuit::isCallEvent()) {
            $event = new BlockDispenseEvent($this, clone $item);
            $event->call();
            if ($event->isCancelled()) return;
        }

        $side = $this->getSide($this->getFacing());
        $tile = $this->getPosition()->getWorld()->getTile($side->getPosition());
        if ($tile instanceof Container) {
            $targetInventory = $tile->getRealInventory();
            $pop = $item->pop();
            if ($targetInventory->canAddItem($pop)) {
                $targetInventory->addItem($pop);
                $inventory->setItem($slot, $item);
            }
            return;
        }

        $result = $this->dispense($item);
        $inventory->setItem($slot, $item);
        if ($result !== null) $inventory->addItem($result);
        $this->getPosition()->getWorld()->addSound($this->getPosition(), new ClickSound());
    }

    public function dispense(Item $item): ?Item {
        return BlockDispenser::$default->dispense($this, $item);
    }
}