<?php

namespace redstone\blockEntities;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\tile\Tile;

use redstone\utils\Facing;

class BlockEntityNoteBlock extends Tile {

    protected $note = 0;
    protected $powered = false;
    
    protected function readSaveData(CompoundTag $nbt) : void {
        if ($nbt->hasTag("note")) {
            $this->note = $nbt->getByte("note");
        }
        if ($nbt->hasTag("powered")) {
            $this->powered = $nbt->getByte("powered") != 0;
        }
    }

    protected function writeSaveData(CompoundTag $nbt) : void {
        $nbt->setByte("note", $this->note);
        $nbt->setByte("powered", $this->powered ? 1 : 0);
    }

    public function getName() : string {
        return "NoteBlock";
    }

    public function getPitch() : int {
        return $this->note;
    }

    public function addPitch() : void {
        $this->note++;
        if ($this->note > 24) {
            $this->note = 0;
        }
    }

    public function getSound() : int {
        $down = $this->getBlock()->getSide(Facing::DOWN);
        switch ($down->getId()) {
            case 5:
            case 17:
            case 47:
            case 53:
            case 58:
            case 134:
            case 135:
            case 136:
            case 157:
            case 162: //TODO
                return 1024;
            case 20:
            case 102:
            case 160:
            case 241:
                return 768;
            case 12:
            case 13:
            case 88:
            case 237:
                return 512;
            case 1:
            case 4:
            case 14:
            case 15:
            case 16:
            case 21:
            case 45:
            case 48:
            case 49:
            case 56:
            case 67:
            case 73:
            case 74:
            case 87:
            case 97:
            case 108:
            case 109:
            case 153: //TODO
                return 256;
            default:
                return 0;
        }
    }

    public function isPowered() : bool {
        return $this->powered;
    }

    public function setPowered(bool $powered) : void {
        $this->powered = $powered;
    } 
}