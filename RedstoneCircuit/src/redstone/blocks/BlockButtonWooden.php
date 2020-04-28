<?php

namespace redstone\blocks;

use pocketmine\block\BlockToolType;

use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Arrow;

use pocketmine\math\AxisAlignedBB;

use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

class BlockButtonWooden extends BlockButtonBase {
    
    protected $id = self::WOODEN_BUTTON;

    public function getName() : string {
        return "Wooden Button";
    }
    
    public function getHardness() : float {
        return 0.5;
    }

    public function getToolType() : int {
        return BlockToolType::TYPE_AXE;
    }

    public function onScheduledUpdate() : void {
        if ($this->getDamage() < 8) {
            return;
        }
        
        $entities = $this->getLevel()->getNearbyEntities($this->bb());
        for ($i = 0; $i < count($entities); ++$i) {
            if ($entities[$i] instanceof Arrow) {
                return;
            }
        }

        $this->setDamage($this->getDamage() - 8);
        $this->getLevel()->setBlock($this, $this);
        $this->getLevel()->broadcastLevelSoundEvent($this->add(0.5, 0.5, 0.5), LevelSoundEventPacket::SOUND_POWER_OFF);
        
        $this->updateAroundRedstone($this);
        $this->updateAroundRedstone($this->asVector3()->getSide($this->getFace()));
    }

    public function onEntityCollide(Entity $entity) : void {
        if (!($entity instanceof Arrow)) {
            return;
        }

        if (!$this->isPowerSource()) {
            $this->setDamage($this->getDamage() + 8);
            $this->getLevel()->setBlock($this, $this);
            $this->getLevel()->broadcastLevelSoundEvent($this->add(0.5, 0.5, 0.5), LevelSoundEventPacket::SOUND_POWER_ON);
            $this->updateAroundRedstone($this);
            $this->updateAroundRedstone($this->asVector3()->getSide($this->getFace()));
        }

        $this->getLevel()->scheduleDelayedBlockUpdate($this, 1);
    }

    public function hasEntityCollision() : bool {
        return true;
    }

    public function getBoundingBox() : ?AxisAlignedBB {
        return null;
    }

    public function getCollisionBoxes() : array {
        return [];
    }
    
    protected function bb() : AxisAlignedBB {
        return new AxisAlignedBB(
            $this->x,
            $this->y,
            $this->z,
            $this->x + 1,
            $this->y + 1,
            $this->z + 1
        );
    }
    
    /** 
     * @return int
     */
    public function getActivateTime() : int {
        return 30;
    }
}