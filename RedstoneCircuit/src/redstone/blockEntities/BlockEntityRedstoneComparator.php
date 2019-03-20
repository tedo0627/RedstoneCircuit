<?php

namespace redstone\blockEntities;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\tile\Tile;

class BlockEntityRedstoneComparator extends Tile {

    protected $outputSignal = 0;

    protected function readSaveData(CompoundTag $nbt) : void {
        if ($nbt->hasTag("outputSignal")) {
            $this->outputSignal = $nbt->getInt("outputSignal");
        }
    }

    protected function writeSaveData(CompoundTag $nbt) : void {
        $nbt->setInt("outputSignal", $this->outputSignal);
    }

    public function getOutputSignal() : int {
        return $this->outputSignal;
    }

    public function setOutputSignal(int $signal) : void {
        $this->outputSignal = $signal;
    }
}