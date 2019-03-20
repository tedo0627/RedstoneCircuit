<?php

namespace redstone\blockEntities;

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

    public function __construct(Level $level, CompoundTag $nbt){
        parent::__construct($level, $nbt);
        $this->scheduleUpdate();
    }

    protected function readSaveData(CompoundTag $nbt) : void {
    }

    protected function writeSaveData(CompoundTag $nbt) : void {
    }

    public function getName() : string{
        return "PistonArm";
    }

    public $c = 0;

    public function onUpdate() : bool {
        if ($this->c > 0) {
            echo 2;
            if ($this->c == 2) {
                echo 3;
                $pk = new LevelSoundEventPacket();
                $pk->sound = 84;
                $pk->position = $this;
                $pk->extraData = -1;
                $pk->entityType = ":";
                $pk->isBabyMob = false;
                $pk->disableRelativeVolume = false;
                $this->level->addChunkPacket($this->x >> 4, $this->z >> 4, $pk);
            }
            $this->spawnToAll();
            $this->c--;
        }
        return true;
    }

    public function serialized() : string{
        $stream = new NetworkLittleEndianNBTStream();

        return $stream->write($this->getNBT());
    }
    
    public function createSpawnPacket() : BlockEntityDataPacket{
        $pk = new BlockEntityDataPacket();
        $pk->x = $this->x;
        $pk->y = $this->y;
        $pk->z = $this->z;
        $pk->namedtag = $this->serialized();

        $stream = new NetworkLittleEndianNBTStream();
        var_dump($stream->read($pk->namedtag));
        return $pk;
    }
    
    public function getNBT() : CompoundTag {
        $nbt = new CompoundTag();
        $nbt->setFloat("LastProgress", 0);
        $list = new ListTag("BreakBlocks", [new IntTag("", 0)], NBT::TAG_Int);
        $list->push(new IntTag("", 1));
        $nbt->setTag($list);
        $nbt->setTag(new ListTag("AttachedBlocks", [new IntTag("", 0)], NBT::TAG_Int));

        return $nbt;
    }

    protected function addAdditionalSpawnData(CompoundTag $nbt) : void {
    }
}