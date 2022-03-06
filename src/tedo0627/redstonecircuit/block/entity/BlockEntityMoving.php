<?php

namespace tedo0627\redstonecircuit\block\entity;

use pocketmine\block\tile\Spawnable;
use pocketmine\nbt\tag\CompoundTag;
use tedo0627\redstonecircuit\block\MovingBlockTrait;

class BlockEntityMoving extends Spawnable {
    use MovingBlockTrait;

    public function readSaveData(CompoundTag $nbt): void {
        $this->setPistonPosX($nbt->getInt("pistonPosX", 0));
        $this->setPistonPosY($nbt->getInt("pistonPosY", 0));
        $this->setPistonPosZ($nbt->getInt("pistonPosZ", 0));

        $tag = $nbt->getCompoundTag("movingBlock");
        if ($tag !== null) {
            $this->setMovingBlockName($tag->getString("name", "minecraft:air"));
            $this->setMovingBlockStates($tag->getCompoundTag("states"));
        }

        $tag = $nbt->getCompoundTag("movingEntity");
        if ($tag !== null) $this->setMovingEntity($tag);
    }

    protected function writeSaveData(CompoundTag $nbt): void {
        $nbt->setInt("pistonPosX", $this->getPistonPosX());
        $nbt->setInt("pistonPosY", $this->getPistonPosY());
        $nbt->setInt("pistonPosZ", $this->getPistonPosZ());

        $tag = new CompoundTag();
        $tag->setString("name", $this->getMovingBlockName());
        $tag->setTag("states", $this->getMovingBlockStates());
        $nbt->setTag("movingBlock", $tag);

        $tag = $this->getMovingEntity();
        if ($tag !== null) $nbt->setTag("movingEntity", $this->getMovingEntity());
    }

    protected function addAdditionalSpawnData(CompoundTag $nbt): void {
        $nbt->setInt("pistonPosX", $this->getPistonPosX());
        $nbt->setInt("pistonPosY", $this->getPistonPosY());
        $nbt->setInt("pistonPosZ", $this->getPistonPosZ());

        $tag = new CompoundTag();
        $tag->setString("name", $this->getMovingBlockName());
        $tag->setTag("states", $this->getMovingBlockStates());
        $nbt->setTag("movingBlock", $tag);

        $tag = $this->getMovingEntity();
        if ($tag !== null) $nbt->setTag("movingEntity", $this->getMovingEntity());
    }
}