<?php

namespace tedo0627\redstonecircuit\block\entity;

use pocketmine\block\tile\Spawnable;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use tedo0627\redstonecircuit\block\PistonTrait;

class BlockEntityPistonArm extends Spawnable {
    use PistonTrait;

    protected bool $sticky = false;

    public function readSaveData(CompoundTag $nbt): void {
        $this->setProgress($nbt->getFloat("Progress", 0.0));
        $this->setLastProgress($nbt->getFloat("LastProgress", 0.0));
        $this->setState($nbt->getByte("State", 0));
        $this->setNewState($nbt->getByte("NewState", 0));
        $this->setSticky($nbt->getByte("Sticky", 0));
        $list = $nbt->getListTag("BreakBlocks");
        if ($list !== null && $list->getTagType() === NBT::TAG_Int) $this->setBreakBlocks($list->getAllValues());
        $list = $nbt->getListTag("AttachedBlocks");
        if ($list !== null && $list->getTagType() === NBT::TAG_Int) $this->setAttachedBlocks($list->getAllValues());
        $list = $nbt->getListTag("HideAttached");
        if ($list !== null && $list->getTagType() === NBT::TAG_Int) $this->setHideAttachedBlocks($list->getAllValues());
    }

    protected function writeSaveData(CompoundTag $nbt): void {
        $nbt->setFloat("Progress", $this->getProgress());
        $nbt->setFloat("LastProgress", $this->getLastProgress());
        $nbt->setByte("State", $this->getState());
        $nbt->setByte("NewState", $this->getNewState());
        $nbt->setByte("Sticky", $this->isSticky());
        $tags = [];
        foreach ($this->getBreakBlocks() as $i) $tags[] = new IntTag($i);
        $nbt->setTag("BreakBlocks", new ListTag($tags, NBT::TAG_Int));
        $tags = [];
        foreach ($this->getAttachedBlocks() as $i) $tags[] = new IntTag($i);
        $nbt->setTag("AttachedBlocks", new ListTag($tags, NBT::TAG_Int));
        $tags = [];
        foreach ($this->getHideAttachedBlocks() as $i) $tags[] = new IntTag($i);
        $nbt->setTag("HideAttached", new ListTag($tags, NBT::TAG_Int));
    }

    protected function addAdditionalSpawnData(CompoundTag $nbt): void {
        $nbt->setFloat("Progress", $this->getProgress());
        $nbt->setFloat("LastProgress", $this->getLastProgress());
        $nbt->setByte("State", $this->getState());
        $nbt->setByte("NewState", $this->getNewState());
        $nbt->setByte("Sticky", $this->isSticky());
        $tags = [];
        foreach ($this->getBreakBlocks() as $i) $tags[] = new IntTag($i);
        $nbt->setTag("BreakBlocks", new ListTag($tags, NBT::TAG_Int));
        $tags = [];
        foreach ($this->getAttachedBlocks() as $i) $tags[] = new IntTag($i);
        $nbt->setTag("AttachedBlocks", new ListTag($tags, NBT::TAG_Int));
    }

    public function isSticky(): bool {
        return $this->sticky;
    }

    public function setSticky(bool $sticky): void {
        $this->sticky = $sticky;
    }
}