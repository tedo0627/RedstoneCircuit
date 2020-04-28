<?php

namespace redstone\blocks;

use pocketmine\Player;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\TripwireHook;

use pocketmine\item\Item;

use pocketmine\math\Vector3;

use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

use redstone\utils\Facing;

class BlockTripwireHook extends TripwireHook implements IRedstone {
    use RedstoneTrait;
    
    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool {
        if ($face == Facing::UP || $face == Facing::DOWN) {
            return false;
        }

        $faces = [
            Facing::SOUTH => 0,
            Facing::WEST => 1,
            Facing::NORTH => 2,
            Facing::EAST => 3
        ];
        
        $this->setDamage($faces[$face]);

        $side = $this->getSide(Facing::opposite($this->getFace()));
        if (!$side->isSolid() || $side->isTransparent()) {
            return false;
        }

        $this->getLevel()->setBlock($this, $this, true, true);

        $block = $this;
        $blocks = [];
        $hook = null;
        for ($i = 0; $i <= 40; ++$i) {
            $block = $block->getSide($this->getFace());
            if ($block instanceof BlockTripwire) {
                $blocks[] = $block;
                continue;
            }
            if ($block instanceof BlockTripwireHook) {
                $hook = $block;
            }
            break;
        }
        
        if ($hook != null) {
            $level = $this->getLevel();
            $pk = new LevelSoundEventPacket();
            $pk->sound = LevelSoundEventPacket::SOUND_ATTACH;

            $this->setDamage($this->getDamage() % 4 + 4);
            $level->setBlock($this, $this);
            $pk->position = $this->add(0.5, 0.1, 0.5);
            $level->addChunkPacket($pk->position->getFloorX() >> 4, $pk->position->getFloorZ() >> 4, $pk);
            
            $hook->setDamage($hook->getDamage() % 4 + 4);
            $level->setBlock($hook, Block::get(0)); // It does not move unless I write this for some reason
            $level->setBlock($hook, $hook);
            $pk->position = $hook->add(0.5, 0.1, 0.5);
            $level->addChunkPacket($pk->position->getFloorX() >> 4, $pk->position->getFloorZ() >> 4, $pk);

            for ($i = 0; $i < count($blocks); ++$i) {
                $block = $blocks[$i];
                if ($block->getDamage() == 5) {
                    continue;
                }
                $block->setDamage(4);
                $level->setBlock($block, $block);
            }
        }
        return true;
    }
    
    public function onBreak(Item $item, Player $player = null) : bool {
        $this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR));
        $this->updateAroundRedstone($this);
        $this->updateAroundRedstone($this->asVector3()->getSide($this->getFace()));

        if ($this->getDamage() & 8 == 0x08) {
            return true;
        }
        
        $face = $this->getFace();
        $blocks = [];
        $block = $this;
        $hook = null;
        for ($j = 0; $j <= 40; ++$j) {
            $block = $block->getSide($face);
            if ($block instanceof BlockTripwire) {
                $blocks[] = $block;
                continue;
            }
            if (!($block instanceof BlockTripwireHook)) {
                break;
            }
            if ($face != Facing::opposite($block->getFace())) {
                break;
            }

            $level = $this->getLevel();
            $pk = new LevelSoundEventPacket();
            $pk->sound = LevelSoundEventPacket::SOUND_DETACH;
            $pk->position = $this->add(0.5, 0.1, 0.5);
            $level->addChunkPacket($pk->position->getFloorX() >> 4, $pk->position->getFloorZ() >> 4, $pk);

            $block->setDamage($block->getDamage() % 4);
            $level->setBlock($block, $block);
            $block->updateAroundRedstone($block);
            $block->updateAroundRedstone($block->asVector3()->getSide(Facing::opposite($block->getFace())));
            $pk->position = $block->add(0.5, 0.1, 0.5);
            $level->addChunkPacket($pk->position->getFloorX() >> 4, $pk->position->getFloorZ() >> 4, $pk);

            for ($k = 0; $k < count($blocks); ++$k) {
                $block = $blocks[$k];
                $block->setDamage(0);
                $level->setBlock($block, $block);
            }
            break;
        }
        return true;
    }

    public function onScheduledUpdate() : void {
        $pk = new LevelSoundEventPacket();
        if ($this->getDamage() < 8) {
            $pk->sound = LevelSoundEventPacket::SOUND_DETACH;
        } else {
            $pk->sound = LevelSoundEventPacket::SOUND_POWER_OFF;
        }
        $pk->position = $this->add(0.5, 0.1, 0.5);
        $this->getLevel()->addChunkPacket($pk->position->getFloorX() >> 4, $pk->position->getFloorZ() >> 4, $pk);

        $this->setDamage($this->getDamage() % 4);
        $this->getLevel()->setBlock($this, $this);

        $this->updateAroundRedstone($this);
        $this->updateAroundRedstone($this->asVector3()->getSide(Facing::opposite($this->getFace())));
    }

	public function onNearbyBlockChange() : void{
        $side = $this->getSide(Facing::opposite($this->getFace()));
        if ($side->isSolid() && !$side->isTransparent()) {
            return;
        }
        $this->getLevel()->useBreakOn($this);
	}

    public function getFace() : int {
        $faces = [
            0 => Facing::SOUTH,
            1 => Facing::WEST,
            2 => Facing::NORTH,
            3 => Facing::EAST
        ];
        return $faces[$this->getDamage() % 4];
    }
    
    public function getStrongPower(int $face) : int {
        if ($face == $this->getFace()) {
            return $this->getWeakPower($face);
        }
        return 0;
    }

    public function getWeakPower(int $face) : int {
        return $this->getDamage() < 8 ? 0 : 15;
    }

    public function isPowerSource() : bool {
        return $this->getDamage() >= 8;
    }

    public function onRedstoneUpdate() : void {
    }
}