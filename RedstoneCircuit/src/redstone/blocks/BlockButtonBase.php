<?php

namespace redstone\blocks;

use pocketmine\Player;

use pocketmine\item\Item;

use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\block\Block;
use pocketmine\block\Transparent;
use pocketmine\block\BlockFactory;

use pocketmine\math\Vector3;

use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

use redstone\utils\Facing;

abstract class BlockButtonBase extends Transparent implements IRedstone {
    use RedstoneTrait;

    public function __construct(int $meta = 0) {
        $this->meta = $meta;
    }
    
    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool {
        $this->setDamage($face);
        
        $side = $this->getSide($this->getFace());
        if ($side instanceof Stair) {
            if ($side->getDamage() >= 4 && $face != Facing::UP) {
                return false;
            } elseif ($side->getDamage() < 4) {
                return false;
            }
        } elseif ($side instanceof Slab) {
            if ($side->getDamage() < 8 && $face != Facing::DOWN) {
                return false;
            } elseif ($side->getDamage() >= 8 && $face != Facing::UP) {
                return false;
            }
        } elseif (!$side->isSolid() || $side->isTransparent()) {
            return false;
        }
        return $this->level->setBlock($this, $this);
    }
    
    public function onBreak(Item $item, Player $player = null) : bool {
        $this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR));
        $this->updateAroundRedstone($this);
        $this->updateAroundRedstone($this->asVector3()->getSide($this->getFace()));
        return true;
    }
    
    public function onActivate(Item $item, Player $player = null) : bool {
        if ($this->isPowerSource()) {
            return true;
        }

        $this->setDamage($this->getDamage() + 8);
        $this->level->setBlock($this, $this);
        $this->level->broadcastLevelSoundEvent($this->add(0.5, 0.5, 0.5), LevelSoundEventPacket::SOUND_POWER_ON);
        $this->updateAroundRedstone($this);
        $this->updateAroundRedstone($this->asVector3()->getSide($this->getFace()));

        $this->level->scheduleDelayedBlockUpdate($this, $this->getActivateTime());
        return true;
    }

    public function onScheduledUpdate() : void {
        if (!$this->isPowerSource()) {
            return;
        }
        $this->setDamage($this->getDamage() - 8);
        $this->level->setBlock($this, $this);
        $this->level->broadcastLevelSoundEvent($this->add(0.5, 0.5, 0.5), LevelSoundEventPacket::SOUND_POWER_OFF);
        
        $this->updateAroundRedstone($this);
        $this->updateAroundRedstone($this->asVector3()->getSide($this->getFace()));
    }

    public function onNearbyBlockChange() : void {
        $damage = $this->getDamage();
        $side = $this->getSide($this->getFace());
        if ($side instanceof Stair) {
            if ($side->getDamage() >= 4 && $this->getFace() == Facing::DOWN) {
                return;
            }
        } elseif ($side instanceof Slab) {
            if ($side->getDamage() < 8 && $this->getFace() == Facing::UP) {
                return;
            } elseif ($side->getDamage() >= 8 && $this->getFace() == Facing::DOWN) {
                return;
            }
        } elseif ($side->isSolid() && !$side->isTransparent()) {
            return;
        }
        $this->level->useBreakOn($this);
    }
    
    public function getVariantBitmask() : int {
        return 5;
    }

    public function isSolid() : bool {
        return false;
    }

    public function canPassThrough() : bool {
        return true;
    }
    
    public function getFace() : int {
        $damage = $this->getDamage();
        if ($damage >= 8) {
            $damage -= 8;
        }
        return Facing::opposite($damage);
    }
    
    public abstract function getActivateTime() : int;

    public function getStrongPower(int $face) : int {
        return $this->isPowerSource() ? 15 : 0;
    }

    public function getWeakPower(int $face) : int {
        return $this->isPowerSource() ? 15 : 0;
    }

    public function isPowerSource() : bool {
        return $this->getDamage() >= 8;
    }

    public function onRedstoneUpdate() : void {
    }
}