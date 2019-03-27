<?php

namespace redstone\blockEntities;

use pocketmine\block\Block;

use pocketmine\inventory\InventoryHolder;

use pocketmine\item\Item;

use pocketmine\level\Level;

use pocketmine\math\Vector3;

use pocketmine\nbt\NBT;
use pocketmine\nbt\NetworkLittleEndianNBTStream;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\IntTag;

use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\BlockEntityDataPacket;

use pocketmine\tile\Spawnable;

class BlockEntityPistonArm extends Spawnable {

    protected $progress = 0;
    protected $lastProgress = 0;

    protected $state = 0;
    protected $newState = 0;

    protected $sticky = 0;

    protected $extend = false;

    protected function readSaveData(CompoundTag $nbt) : void {
        if ($nbt->hasTag("Progress")) {
            $this->progress = $nbt->getFloat("Progress");
        }

        if ($nbt->hasTag("LastProgress")) {
            $this->lastProgress = $nbt->getFloat("LastProgress");
        }

        if ($nbt->hasTag("State")) {
            $this->state = $nbt->getByte("State");
        }

        if ($nbt->hasTag("NewState")) {
            $this->newState = $nbt->getByte("NewState");
        }

        if ($nbt->hasTag("Sticky")) {
            $this->sticky = $nbt->getByte("Sticky");
        }

        $this->scheduleUpdate();
    }

    protected function writeSaveData(CompoundTag $nbt) : void {
        $nbt->setFloat("Progress", $this->progress);
        $nbt->setFloat("LastProgress", $this->lastProgress);

        $nbt->setByte("State", $this->state);
        $nbt->setByte("NewState", $this->newState);

        $nbt->setByte("Sticky", $this->sticky);
    }

    public function getName() : string{
        return "PistonArm";
    }

    public function onUpdate() : bool {
        if ($this->isClosed()) {
            return false;
        }

        if ($this->extend) {
            if ($this->newState == 0) {
                $this->newState = 1;

                $block = $this->getBlock();
                $side = $this->getSide($block->getFace());
                $this->getLevel()->setBlock($side, Block::get(34));

                $this->onChanged();
                return true;
            }

            if ($this->newState == 1) {
                if ($this->state == 0) {
                    $this->state = 1;
                }

                $this->lastProgress = $this->progress;
                if ($this->progress == 1) {
                    $this->state = 2;
                    $this->newState = 2;
                } else {
                    $this->progress += 0.5;
                }

                $this->onChanged();

                if ($this->progress == 0.5) {
                    $pk = new LevelSoundEventPacket();
                    $pk->sound = 84;
                    $pk->position = $this;
                    $pk->extraData = -1;
                    $pk->entityType = ":";
                    $pk->isBabyMob = false;
                    $pk->disableRelativeVolume = false;
                    $this->getLevel()->addChunkPacket($this->getFloorX() >> 4, $this->getFloorZ() >> 4, $pk);
                }
            }
        } else {
            if ($this->state == 2) {
                $this->state = 3;
                $this->newState = 3;

                $block = $this->getBlock();
                $side = $this->getSide($block->getFace());
                $this->getLevel()->setBlock($side, Block::get(0));

                $this->onChanged();
                return true;
            }

            if ($this->state == 3) {
                $this->lastProgress = $this->progress;
                if ($this->progress == 0) {
                    $this->state = 0;
                    $this->newState = 0;
                } else {
                    $this->progress -= 0.5;
                }

                $this->onChanged();

                if ($this->progress == 0.5) {
                    $pk = new LevelSoundEventPacket();
                    $pk->sound = 83;
                    $pk->position = $this;
                    $pk->extraData = -1;
                    $pk->entityType = ":";
                    $pk->isBabyMob = false;
                    $pk->disableRelativeVolume = false;
                    $this->getLevel()->addChunkPacket($this->getFloorX() >> 4, $this->getFloorZ() >> 4, $pk);
                }
            }
        }

        return true;
    }

    protected function addAdditionalSpawnData(CompoundTag $nbt) : void {
        $nbt->setFloat("Progress", $this->progress);
        $nbt->setFloat("LastProgress", $this->lastProgress);
        $nbt->setByte("State", $this->state);
        $nbt->setByte("NewState", $this->newState);
        $nbt->setByte("Sticky", $this->sticky);
        $list = new ListTag("BreakBlocks", [], NBT::TAG_Int);
        $nbt->setTag($list);
        $list = new ListTag("AttachedBlocks", [], NBT::TAG_Int);
        $nbt->setTag($list);
    }

    public function extend(bool $extend) : void {
        $this->extend = $extend;
    }

    public function getProgress() : int {
        return $this->progress;
    }

    public function getLastProgress() : int {
        return $this->lastProgress;
    }

    public function getState() : int {
        return $this->state;
    }

    public function getNewState() : int {
        return $this->newState;
    }

    public function isSticky() : bool {
        return $this->sticky == 1;
    }
}