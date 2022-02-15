<?php

namespace tedo0627\redstonecircuit\block\entity;

use pocketmine\block\tile\Tile;
use pocketmine\nbt\tag\CompoundTag;

class BlockEntityObserver extends Tile {

    protected int $blockId = 0;
    protected int $stateMeta = 0;

    public function readSaveData(CompoundTag $nbt): void {
        $this->blockId = $nbt->getInt("blockId", 0);
        $this->stateMeta = $nbt->getInt("stateMeta", 0);
    }

    protected function writeSaveData(CompoundTag $nbt): void {
        $nbt->setInt("blockId", $this->blockId);
        $nbt->setInt("stateMeta", $this->stateMeta);
    }

    public function getBlockId(): int {
        return $this->blockId;
    }

    public function setBlockId(int $id): void {
        $this->blockId = $id;
    }

    public function getStateMeta(): int {
        return $this->stateMeta;
    }

    public function setStateMeta(int $meta): void {
        $this->stateMeta = $meta;
    }
}