<?php

namespace redstone\blocks;

use pocketmine\Player;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\Transparent;

use pocketmine\entity\Entity;

use pocketmine\item\Item;

use pocketmine\math\Vector3;
use pocketmine\math\AxisAlignedBB;

use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

use redstone\utils\Facing;

abstract class BlockPressurePlateBase extends Transparent implements IRedstone {
    use RedstoneTrait;
    
    public function __construct(int $meta = 0){
        $this->meta = $meta;
    }
    
    public function getVariantBitmask() : int {
        return 0;
    }
    
    public function getHardness() : float {
        return 0.5;
    }

    public function isSolid() : bool {
        return false;
    }

    public function hasEntityCollision() : bool {
        return true;
    }

    public function canPassThrough() : bool {
        return true;
    }
    
    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool {
        $under = $this->getSide(Facing::DOWN);
        if (!$under->isSolid() || $under->isTransparent()) {
            return false;
        }

        $this->getLevel()->setBlock($blockReplace, $this);
        $this->updateAroundRedstone($this);
        $this->updateAroundRedstone($this->asVector3()->getSide(Facing::DOWN));
        return true;
    }

    public function onBreak(Item $item, Player $player = null) : bool {
        $this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR));
        $this->updateAroundRedstone($this);
        $this->updateAroundRedstone($this->asVector3()->getSide(Facing::DOWN));
        return true;
    }

    public function onNearbyBlockChange() : void {
        $under = $this->getSide(Facing::DOWN);
        if ($under->isSolid() && !$under->isTransparent()) {
            return;
        }
        $this->level->useBreakOn($this);
    }

    public function onScheduledUpdate() : void {
        $damage = $this->computeDamage();
        if ($this->getDamage() != $damage) {
            if ($damage == 0) {
                $this->level->broadcastLevelSoundEvent($this->add(0.5, 0.5, 0.5), LevelSoundEventPacket::SOUND_POWER_OFF, $this->getOffSoundExtraData());
            }
            $this->setDamage($damage);
            $this->level->setBlock($this, $this);
            $this->updateAroundRedstone($this);
            $this->updateAroundRedstone($this->asVector3()->getSide(Facing::DOWN));
        }

        if ($damage > 0) {
            $this->level->scheduleDelayedBlockUpdate($this, $this->getDelay());
        }
    }

    public function onEntityCollide(Entity $entity) : void {
        $damage = $this->computeDamage();
        if ($damage <= 0) {
            return;
        }

        if ($this->getDamage() != $damage) {
            if ($this->getDamage() == 0) {
                $this->level->broadcastLevelSoundEvent($this->add(0.5, 0.5, 0.5), LevelSoundEventPacket::SOUND_POWER_ON, $this->getOnSoundExtraData());
            }
            $this->setDamage($damage);
            $this->level->setBlock($this, $this);
            $this->updateAroundRedstone($this);
            $this->updateAroundRedstone($this->asVector3()->getSide(Facing::DOWN));
        }
        $this->level->scheduleDelayedBlockUpdate($this, $this->getDelay());
    }

    public function getBoundingBox() : ?AxisAlignedBB {
        return null;
    }

    public function getCollisionBoxes() : array {
        return [];
    }
    
    protected function bb() : AxisAlignedBB {
        return new AxisAlignedBB(
            $this->x + 0.0625,
            $this->y,
            $this->z + 0.0625,
            $this->x + 0.9375,
            $this->y + 0.0625,
            $this->z + 0.9375
        );
    }

    public abstract function computeDamage() : int;

    public abstract function getDelay() : int;

    public abstract function getOnSoundExtraData() : int;

    public abstract function getOffSoundExtraData() : int;

    public function getStrongPower(int $face) : int {
        if (!$this->isPowerSource()) {
            return 0;
        }
        if ($face == Facing::UP) {
            return 15;
        }
        return 0;
    }

    public function getWeakPower(int $face) : int {
        if (!$this->isPowerSource()) {
            return 0;
        }
        return 15;
    }

    public function isPowerSource() : bool {
        return $this->getDamage() > 0;
    }

    public function onRedstoneUpdate() : void {
    }
}