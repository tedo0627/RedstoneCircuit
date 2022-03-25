<?php

namespace tedo0627\redstonecircuit\block\entity;

use pocketmine\block\tile\Tile;
use pocketmine\nbt\tag\CompoundTag;
use tedo0627\redstonecircuit\block\BlockEntityInitializeTrait;

class BlockEntityObserver extends Tile implements IgnorePiston {
    use BlockEntityInitializeTrait;

    protected int $blockId = -1;
    protected int $stateMeta = -1;

    public function readSaveData(CompoundTag $nbt): void {
        $this->blockId = $nbt->getInt("blockId", -1);
        $this->stateMeta = $nbt->getInt("stateMeta", -1);
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