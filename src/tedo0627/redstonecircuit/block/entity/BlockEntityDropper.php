<?php

namespace tedo0627\redstonecircuit\block\entity;

use pocketmine\block\tile\Container;
use pocketmine\block\tile\ContainerTrait;
use pocketmine\block\tile\Nameable;
use pocketmine\block\tile\NameableTrait;
use pocketmine\block\tile\Spawnable;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;
use tedo0627\redstonecircuit\block\inventory\DropperInventory;

class BlockEntityDropper extends Spawnable implements Container, Nameable {
    use NameableTrait;
    use ContainerTrait;

    protected DropperInventory $inventory;

    public function __construct(World $world, Vector3 $pos) {
        parent::__construct($world, $pos);
        $this->inventory = new DropperInventory($this->getPosition());
    }

    public function readSaveData(CompoundTag $nbt): void {
        $this->loadName($nbt);
        $this->loadItems($nbt);
    }

    protected function writeSaveData(CompoundTag $nbt): void {
        $this->saveName($nbt);
        $this->saveItems($nbt);
    }

    /**
     * @return DropperInventory
     */
    public function getInventory() {
        return $this->inventory;
    }

    /**
     * @return DropperInventory
     */
    public function getRealInventory() {
        return $this->inventory;
    }

    public function getDefaultName(): string {
        return "Dropper";
    }
}