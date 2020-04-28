<?php

namespace redstone\blockEntities;

use pocketmine\inventory\InventoryHolder;

use pocketmine\item\Item;

use pocketmine\math\Vector3;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\network\mcpe\protocol\LevelEventPacket;

use pocketmine\tile\Container;
use pocketmine\tile\ContainerTrait;
use pocketmine\tile\Nameable;
use pocketmine\tile\NameableTrait;
use pocketmine\tile\Spawnable;

use redstone\inventories\DropperInventory;

use function count;
use function mt_rand;

class BlockEntityDropper extends Spawnable implements InventoryHolder, Container, Nameable {
    use NameableTrait {
        addAdditionalSpawnData as addNameSpawnData;
    }
    use ContainerTrait;

    protected $inventory;

    protected function readSaveData(CompoundTag $nbt) : void {
        $this->loadName($nbt);

        $this->inventory = new DropperInventory($this);
        $this->loadItems($nbt);
    }

    protected function writeSaveData(CompoundTag $nbt) : void {
        $this->saveName($nbt);
        $this->saveItems($nbt);
    }

    public function getName() : string{
        return "Dropper";
    }
        
    public function getDefaultName() : string{
        return "Dropper";
    }
        
    public function getInventory() {
        return $this->inventory;
    }
        
    public function getRealInventory() {
        return $this->inventory;
    }

    public function dropItem() : void {
        $inventory = $this->getInventory();
        $random = [];
        for ($i = 0; $i < 9; ++$i) {
            $item = $inventory->getItem($i);
            if ($item->getId() != 0) {
                $random[] = $i;
            }
        }

        if (count($random) == 0) {
            $this->level->broadcastLevelEvent($this->add(0.5, 0.5, 0.5), LevelEventPacket::EVENT_SOUND_CLICK_FAIL, 1200);
            return;
        }

        $slot = $random[mt_rand(0, count($random) - 1)];
        $item = $inventory->getItem($slot);
        $drop = clone($item);
        $drop->setCount(1);

        $block = $this->getBlock();
        $damage = $block->getDamage();
        $side = $this->getSide($damage > 8 ? $damage - 8 : $damage);
        $tile = $this->getLevel()->getTile($side);
        if ($tile instanceof Container) {
            if ($tile->getInventory()->canAddItem($drop)) {
                $item->setCount($item->getCount() - 1);
                if ($item->getCount() <= 0) {
                    $item = Item::get(0);
                }
                $inventory->setItem($slot, $item);
                $tile->getInventory()->addItem($drop);
            }
            return;
        }

        $item->setCount($item->getCount() - 1);
        if ($item->getCount() <= 0) {
            $item = Item::get(0);
        }
        $inventory->setItem($slot, $item);

        $motion = new Vector3();
        $motion = $motion->getSide($block->getdamage());
        $this->level->dropItem(new Vector3($this->x + $motion->x * 2 + 0.5, $this->y + ($motion->y < 0 ? $motion->y : 0.5), $this->z + $motion->z * 2 + 0.5), $drop, $motion->multiply(0.3));
        $this->level->broadcastLevelEvent($this->add(0.5, 0.5, 0.5), LevelEventPacket::EVENT_SOUND_CLICK, 1000);
    }
}