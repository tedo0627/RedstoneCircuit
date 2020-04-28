<?php

namespace redstone\blocks;

use pocketmine\Player;

use pocketmine\entity\Entity;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\Tripwire;

use pocketmine\item\Item;
use pocketmine\item\Shears;

use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;

use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

use redstone\utils\Facing;

class BlockTripwire extends Tripwire {

    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool {
        $this->setDamage(2);
        $this->getLevel()->setBlock($this, $this, true, true);

        $directions = [Facing::NORTH, Facing::WEST];
        for ($i = 0; $i < count($directions); ++$i) {
            $face = $directions[$i];
            $blocks = [$this];
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

                if ($hook == null) {
                    $hook = $block;
                    $block = $this;
                    $face = Facing::opposite($face);
                } else {
                    $level = $this->getLevel();
                    $pk = new LevelSoundEventPacket();
                    $pk->sound = LevelSoundEventPacket::SOUND_ATTACH;
        
                    $block->setDamage($block->getDamage() ^ 4);
                    $level->setBlock($block, $block);
                    $pk->position = $block->add(0.5, 0.1, 0.5);
                    $level->addChunkPacket($pk->position->getFloorX() >> 4, $pk->position->getFloorZ() >> 4, $pk);
                    
                    $hook->setDamage($hook->getDamage() ^ 4);
                    $level->setBlock($hook, $hook);
                    $pk->position = $hook->add(0.5, 0.1, 0.5);
                    $level->addChunkPacket($pk->position->getFloorX() >> 4, $pk->position->getFloorZ() >> 4, $pk);

                    for ($k = 0; $k < count($blocks); ++$k) {
                        $block = $blocks[$k];
                        if ($block->getDamage() == 5) {
                            continue;
                        }
                        $block->setDamage(4);
                        $level->setBlock($block, $block);
                    }
                    break;
                }
            }
        }
        return true;
    }
    
    public function onBreak(Item $item, Player $player = null) : bool {
        if ($this->getDamage() != 5 && $this->getDamage() != 4) {
            $this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR));
            return true;
        }

        $directions = [Facing::NORTH, Facing::WEST];
        for ($i = 0; $i < count($directions); ++$i) {
            $face = $directions[$i];
            $blocks = [$this];
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

                if ($hook == null) {
                    $hook = $block;
                    $block = $this;
                    $face = Facing::opposite($face);
                } else {
                    $level = $this->getLevel();

                    if ($item instanceof Shears) {
                        if ($block->getDamage() >= 8) {
                            $pk = new LevelSoundEventPacket();
                            $pk->sound = LevelSoundEventPacket::SOUND_POWER_OFF;
                            $pk->position = $block->add(0.5, 0.1, 0.5);
                            $level->addChunkPacket($pk->position->getFloorX() >> 4, $pk->position->getFloorZ() >> 4, $pk);
                            $pk->position = $hook->add(0.5, 0.1, 0.5);
                            $level->addChunkPacket($pk->position->getFloorX() >> 4, $pk->position->getFloorZ() >> 4, $pk);
                        }
            
                        $block->setDamage($block->getDamage() % 4);
                        $level->setBlock($block, $block);
                        $block->updateAroundRedstone($block);
                        $block->updateAroundRedstone($block->asVector3()->getSide(Facing::opposite($block->getFace())));
                        $level->scheduleDelayedBlockUpdate($block, 10);
                        
                        $hook->setDamage($hook->getDamage() % 4);
                        $level->setBlock($hook, $hook);
                        $hook->updateAroundRedstone($hook);
                        $hook->updateAroundRedstone($hook->asVector3()->getSide(Facing::opposite($hook->getFace())));
                        $level->scheduleDelayedBlockUpdate($hook, 10);

                        for ($k = 0; $k < count($blocks); ++$k) {
                            $block = $blocks[$k];
                            $block->setDamage(0);
                            $level->setBlock($block, $block);
                        }
                        break;
                    }

                    if ($block->getDamage() < 8) {
                        $pk = new LevelSoundEventPacket();
                        $pk->sound = LevelSoundEventPacket::SOUND_ATTACH;
                        $pk->position = $block->add(0.5, 0.1, 0.5);
                        $level->addChunkPacket($pk->position->getFloorX() >> 4, $pk->position->getFloorZ() >> 4, $pk);
                        $pk->position = $hook->add(0.5, 0.1, 0.5);
                        $level->addChunkPacket($pk->position->getFloorX() >> 4, $pk->position->getFloorZ() >> 4, $pk);
                    }
        
                    $block->setDamage($block->getDamage() % 4 + 12);
                    $level->setBlock($block, $block);
                    $block->updateAroundRedstone($block);
                    $block->updateAroundRedstone($block->asVector3()->getSide(Facing::opposite($block->getFace())));
                    $level->scheduleDelayedBlockUpdate($block, 10);
                    
                    $hook->setDamage($hook->getDamage() % 4 + 12);
                    $level->setBlock($hook, $hook);
                    $hook->updateAroundRedstone($hook);
                    $hook->updateAroundRedstone($hook->asVector3()->getSide(Facing::opposite($hook->getFace())));
                    $level->scheduleDelayedBlockUpdate($hook, 10);

                    for ($k = 0; $k < count($blocks); ++$k) {
                        $block = $blocks[$k];
                        $block->setDamage(0);
                        $level->setBlock($block, $block);
                    }
                    break;
                }
            }
        }
        $this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR));
        return true;
    }

    public function onScheduledUpdate() : void {
        if ($this->getDamage() != 5) {
            return;
        }
        
        $entities = $this->getLevel()->getNearbyEntities($this->bb());
        if (count($entities) > 0) {
            return;
        }
        
        $directions = [Facing::NORTH, Facing::WEST];
        for ($i = 0; $i < count($directions); ++$i) {
            $face = $directions[$i];
            $blocks = [$this];
            $block = $this;
            $hook = null;
            for ($j = 0; $j <= 40; ++$j) {
                $block = $block->getSide($face);
                if ($block instanceof BlockTripwire) {
                    $entities = $this->getLevel()->getNearbyEntities($block->bb());
                    if (count($entities) > 0) {
                        return;
                    }
                    $blocks[] = $block;
                    continue;
                }
                if (!($block instanceof BlockTripwireHook)) {
                    break;
                }
                if ($face != Facing::opposite($block->getFace())) {
                    break;
                }

                if ($hook == null) {
                    $hook = $block;
                    $block = $this;
                    $face = Facing::opposite($face);
                } else {
                    $level = $this->getLevel();
                    $pk = new LevelSoundEventPacket();
                    $pk->sound = LevelSoundEventPacket::SOUND_POWER_OFF;
        
                    $block->setDamage($block->getDamage() % 4 + 4);
                    $level->setBlock($block, $block);
                    $block->updateAroundRedstone($block);
                    $block->updateAroundRedstone($block->asVector3()->getSide(Facing::opposite($block->getFace())));
                    $pk->position = $block->add(0.5, 0.1, 0.5);
                    $level->addChunkPacket($pk->position->getFloorX() >> 4, $pk->position->getFloorZ() >> 4, $pk);
                    
                    $hook->setDamage($hook->getDamage() % 4 + 4);
                    $level->setBlock($hook, $hook);
                    $hook->updateAroundRedstone($hook);
                    $hook->updateAroundRedstone($hook->asVector3()->getSide(Facing::opposite($hook->getFace())));
                    $pk->position = $hook->add(0.5, 0.1, 0.5);
                    $level->addChunkPacket($pk->position->getFloorX() >> 4, $pk->position->getFloorZ() >> 4, $pk);

        
                    for ($k = 0; $k < count($blocks); ++$k) {
                        $block = $blocks[$k];
                        $block->setDamage(4);
                        $level->setBlock($block, $block);
                    }
                    break;
                }
            }
        }
    }

    protected function bb() : AxisAlignedBB {
        return new AxisAlignedBB(
            $this->x,
            $this->y,
            $this->z,
            $this->x + 1,
            $this->y + 0.09375,
            $this->z + 1
        );
    }

    public function onEntityCollide(Entity $entity) : void {
        $this->getLevel()->scheduleDelayedBlockUpdate($this, 10);

        if ($this->getDamage() != 4) {
            return;
        }

        $directions = [Facing::NORTH, Facing::WEST];
        for ($i = 0; $i < count($directions); ++$i) {
            $face = $directions[$i];
            $blocks = [$this];
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

                if ($hook == null) {
                    $hook = $block;
                    $block = $this;
                    $face = Facing::opposite($face);
                } else {
                    $level = $this->getLevel();
                    $pk = new LevelSoundEventPacket();
                    $pk->sound = LevelSoundEventPacket::SOUND_POWER_ON;
        
                    $block->setDamage($block->getDamage() % 4 + 12);
                    $level->setBlock($block, $block);
                    $block->updateAroundRedstone($block);
                    $block->updateAroundRedstone($block->asVector3()->getSide(Facing::opposite($block->getFace())));
                    $pk->position = $block->add(0.5, 0.1, 0.5);
                    $level->addChunkPacket($pk->position->getFloorX() >> 4, $pk->position->getFloorZ() >> 4, $pk);
                    
                    $hook->setDamage($hook->getDamage() % 4 + 12);
                    $level->setBlock($hook, $hook);
                    $hook->updateAroundRedstone($hook);
                    $hook->updateAroundRedstone($hook->asVector3()->getSide(Facing::opposite($hook->getFace())));
                    $pk->position = $hook->add(0.5, 0.1, 0.5);
                    $level->addChunkPacket($pk->position->getFloorX() >> 4, $pk->position->getFloorZ() >> 4, $pk);

                    for ($k = 0; $k < count($blocks); ++$k) {
                        $block = $blocks[$k];
                        $block->setDamage(5);
                        $level->setBlock($block, $block);
                    }
                    break;
                }
            }
        }
    }

    public function hasEntityCollision() : bool {
        return true;
    }
}