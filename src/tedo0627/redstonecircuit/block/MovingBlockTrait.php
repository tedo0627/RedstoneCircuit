<?php

namespace tedo0627\redstonecircuit\block;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\tile\Tile;
use pocketmine\nbt\tag\CompoundTag;

trait MovingBlockTrait {

    protected int $pistonPosX = 0;
    protected int $pistonPosY = 0;
    protected int $pistonPosZ = 0;

    protected string $movingBlockName = "minecraft:air";
    protected CompoundTag $movingBlockStates;

    protected ?CompoundTag $movingEntity = null;

    public function getPistonPosX(): Int {
        return $this->pistonPosX;
    }

    public function setPistonPosX(int $x): void {
        $this->pistonPosX = $x;
    }

    public function getPistonPosY(): Int {
        return $this->pistonPosY;
    }

    public function setPistonPosY(int $y): void {
        $this->pistonPosY = $y;
    }

    public function getPistonPosZ(): Int {
        return $this->pistonPosZ;
    }

    public function setPistonPosZ(int $z): void {
        $this->pistonPosZ = $z;
    }

    public function getMovingBlockName(): string {
        return $this->movingBlockName;
    }

    public function setMovingBlockName(string $name): void {
        $this->movingBlockName = $name;
    }

    public function getMovingBlockStates(): CompoundTag {
        return $this->movingBlockStates;
    }

    public function setMovingBlockStates(CompoundTag $tag): void {
        $this->movingBlockStates = $tag;
    }

    public function getMovingEntity(): ?CompoundTag {
        return $this->movingEntity;
    }

    public function setMovingEntity(?CompoundTag $tag): void {
        $this->movingEntity = $tag;
    }

    public function getMovingBlock(): Block {
        $table = BlockTable::getInstance();
        $id = $table->getId($this->getMovingBlockName());
        $damage = $table->getDamage($id, $this->getMovingBlockStates());
        return BlockFactory::getInstance()->get($id, $damage);
    }

    public function setMovingBlock(Block $block, ?Tile $tile = null): void {
        $table = BlockTable::getInstance();
        $this->setMovingBlockName($table->getName($block->getId()));
        $this->setMovingBlockStates($table->getStates($block->getId(), $block->getMeta()));
        if ($tile !== null) $this->setMovingEntity($tile->saveNBT());
    }
}