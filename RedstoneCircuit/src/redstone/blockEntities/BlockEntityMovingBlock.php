<?php

namespace redstone\blockEntities;

use pocketmine\block\Block;

use pocketmine\math\Vector3;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\tile\Spawnable;
use pocketmine\tile\Tile;


use redstone\Main;

class BlockEntityMovingBlock extends Spawnable {

    private $piston;

    private $movingBlock;
    private $movingBlockExtra;

    private $movingEntity;
    
    protected function readSaveData(CompoundTag $nbt) : void {
        $this->piston = new Vector3(0, -1, 0);
        if ($nbt->hasTag("pistonPosX")) {
            $this->piston->x = $nbt->getInt("pistonPosX");
        }
        if ($nbt->hasTag("pistonPosY")) {
            $this->piston->x = $nbt->getInt("pistonPosY");
        }
        if ($nbt->hasTag("pistonPosZ")) {
            $this->piston->x = $nbt->getInt("pistonPosZ");
        }
        
        if ($nbt->hasTag("movingBlock")) {
            $this->movingBlock = $nbt->getTag("movingBlock");
        } else {
            $tag = new CompoundTag("movingBlock");
            $tag->setShort("val", 0);
            $tag->setString("name", "minecraft:air");
            $this->movingBlock = $tag;
        }
        if ($nbt->hasTag("movingBlockExtra")) {
            $this->movingBlockExtra = $nbt->getTag("movingBlockExtra");
        } else {
            $tag = new CompoundTag("movingBlockExtra");
            $tag->setShort("val", 0);
            $tag->setString("name", "minecraft:air");
            $this->movingBlockExtra = $tag;
        }
        if ($nbt->hasTag("movingEntity")) {
            $this->movingEntity = $nbt->getCompoundTag("movingEntity");
        }
    }

    protected function writeSaveData(CompoundTag $nbt) : void {
        $nbt->setInt("pistonPosX", $this->piston->x);
        $nbt->setInt("pistonPosY", $this->piston->y);
        $nbt->setInt("pistonPosZ", $this->piston->z);

        $nbt->setTag($this->movingBlock);
        $nbt->setTag($this->movingBlockExtra);
    }

    public function getName() : string{
        return "movingBlock";
    }

    protected function addAdditionalSpawnData(CompoundTag $nbt) : void {
        $nbt->setInt("pistonPosX", $this->piston->x);
        $nbt->setInt("pistonPosY", $this->piston->y);
        $nbt->setInt("pistonPosZ", $this->piston->z);

        $nbt->setTag($this->movingBlock);
        $nbt->setTag($this->movingBlockExtra);

        if ($this->movingEntity != null) {
            $nbt->setTag($this->movingEntity);
        }
    }

    public function setData(Block $piston, Block $sourceBlock, ?Tile $tile) : void {
        $this->piston->x = $piston->x;
        $this->piston->y = $piston->y;
        $this->piston->z = $piston->z;

        $tag = new CompoundTag("movingBlock");
        $tag->setShort("val", $sourceBlock->getDamage());
        $tag->setString("name", Main::getInstance()->getGlobalBlockPalette()->getNameAt($sourceBlock));
        $this->movingBlock = $tag;

        if ($tile != null) {
            $tag = $tile->saveNBT();
            $tag->setName("movingEntity");
            $tag->setInt("x", $this->x);
            $tag->setInt("y", $this->y);
            $tag->setInt("z", $this->z);
            $this->movingEntity = $tag;
        }

        $this->onChanged();
    }

    public function setBlock() : void {
        $level = $this->getLevel();
        if ($this->movingEntity != null) {
            $tag = $this->movingEntity;
            Tile::createTile($tag->getString("id"), $level, $tag);
        }

        $tag = $this->movingBlock;
        $name = $tag->getString("name");
        $damage = $tag->getShort("val");
        $block = Main::getInstance()->getGlobalBlockPalette()->getBlock($name, $damage);
        $level->setBlock($this, $block);
    }
}