<?php

namespace tedo0627\redstonecircuit\block\entity;

use pocketmine\block\tile\Note;
use pocketmine\nbt\tag\CompoundTag;

class BlockEntityNote extends Note {

    private bool $powered = false;

    public function readSaveData(CompoundTag $nbt): void {
        parent::readSaveData($nbt);
        $this->powered = $nbt->getByte("powered", 0) !== 0;
    }

    public function writeSaveData(CompoundTag $nbt): void {
        parent::writeSaveData($nbt);
        $nbt->setByte("powered", $this->powered ? 1 : 0);
    }

    public function isPowered(): bool {
        return $this->powered;
    }

    public function setPowered(bool $powered): void {
        $this->powered = $powered;
    }
}