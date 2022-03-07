<?php

namespace tedo0627\redstonecircuit\block\entity;

use pocketmine\block\tile\Tile;
use pocketmine\block\utils\AnalogRedstoneSignalEmitterTrait;
use pocketmine\nbt\tag\CompoundTag;

class BlockEntityTarget extends Tile {
    use AnalogRedstoneSignalEmitterTrait;

    public function readSaveData(CompoundTag $nbt): void {
        $this->setOutputSignalStrength($nbt->getInt("power", 0));
    }

    protected function writeSaveData(CompoundTag $nbt): void {
        $nbt->setInt("power", $this->getOutputSignalStrength());
    }
}