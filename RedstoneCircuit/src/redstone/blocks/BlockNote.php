<?php

namespace redstone\blocks;

use pocketmine\Player;

use pocketmine\block\Block;
use pocketmine\block\NoteBlock;

use pocketmine\item\Item;

use pocketmine\math\Vector3;

use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

use pocketmine\tile\Tile;


use redstone\blockEntities\BlockEntityNoteBlock;

use redstone\utils\Facing;

class BlockNote extends NoteBlock implements IRedstone {
    use RedstoneTrait;

    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool {
        $this->getLevel()->setBlock($this, $this);
        
        Tile::createTile("BlockEntityNoteBlock", $this->getLevel(), BlockEntityNoteBlock::createNBT($this->asVector3()));
        return true;
    }

    public function onActivate(Item $item, Player $player = null) : bool {
        if ($this->getSide(Facing::UP)->getId() != 0) {
            return true;
        }

        if($player instanceof Player){
            $tile = $this->getLevel()->getTile($this);
            $note = null;
            if($tile instanceof BlockEntityNoteBlock){
                $note = $tile;
            }else{
                $note = Tile::createTile("BlockEntityNoteBlock", $this->getLevel(), BlockEntityNoteBlock::createNBT($this->asVector3()));
            }
            $note->addPitch();

            $this->playSound();
        }
        return true;
    }
    
    public function playSound() : void {
        $tile = $this->getLevel()->getTile($this);
        $note = null;
        if($tile instanceof BlockEntityNoteBlock){
            $note = $tile;
        }else{
            $note = Tile::createTile("BlockEntityNoteBlock", $this->getLevel(), BlockEntityNoteBlock::createNBT($this->asVector3()));
        }

        $pitch = $note->getPitch();
        $sound = $note->getSound();
        $this->level->broadcastLevelSoundEvent($this->add(0.5, 0.5, 0.5), LevelSoundEventPacket::SOUND_NOTE, $sound + $pitch);

        $pk = new BlockEventPacket();
        $pk->x = $this->getX();
        $pk->y = $this->getY();
        $pk->z = $this->getZ();
        $pk->eventType = 1;
        $pk->eventData = $pitch;

        $this->level->addChunkPacket($this->getX() >> 4, $this->getZ() >> 4, $pk);
    }
    
    public function getStrongPower(int $face) : int {
        return 0;
    }

    public function getWeakPower(int $face) : int {
        return 0;
    }

    public function isPowerSource() : bool {
        return false;
    }

    public function onRedstoneUpdate() : void {
        $tile = $this->getLevel()->getTile($this);
        $note = null;
        if($tile instanceof BlockEntityNoteBlock){
            $note = $tile;
        }else{
            $note = Tile::createTile("BlockEntityNoteBlock", $this->getLevel(), BlockEntityNoteBlock::createNBT($this->asVector3()));
        }

        if ($this->isBlockPowered($this->asVector3()) && !$note->isPowered()) {
            $note->setPowered(true);
            $this->playSound();
        } elseif (!$this->isBlockPowered($this->asVector3()) && $note->isPowered()) {
            $note->setPowered(false);
        }
    }
}