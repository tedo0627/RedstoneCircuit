<?php

namespace redstone\blocks;

use pocketmine\Player;

use pocketmine\item\Item;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\Transparent;

use pocketmine\network\mcpe\protocol\LevelEventPacket;

use pocketmine\math\Vector3;

use redstone\utils\Facing;

class BlockLever extends Transparent implements IRedstone {
    use RedstoneTrait;

    protected $id = self::LEVER;

    public function __construct(int $meta = 0){
        $this->meta = $meta;
    }

    public function getName() : string {
        return "Lever";
    }

    public function getHardness() : float {
        return 0.5;
    }

    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool {
        if(!$blockClicked->isSolid()){
            return false;
        }

        if($face === Vector3::SIDE_DOWN){
            $this->meta = 0;
        }else{
            $this->meta = 6 - $face;
        }

        if($player !== null){
            if(($player->getDirection() & 0x01) === 0){
                if($face === Vector3::SIDE_UP){
                    $this->meta = 6;
                }
            }else{
                if($face === Vector3::SIDE_DOWN){
                    $this->meta = 7;
                }
            }
        }

        $this->level->setBlock($blockReplace, $this, true, true);
        $this->updateAroundRedstone($this);
        $this->updateAroundRedstone($this->asVector3()->getSide($this->getFace()));
        return true;
    }
    
    public function onBreak(Item $item, Player $player = null) : bool {
        $this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR));
        $this->updateAroundRedstone($this);
        $this->updateAroundRedstone($this->asVector3()->getSide($this->getFace()));
        return true;
    }
    
    public function onActivate(Item $item, Player $player = null) : bool {
        if (!$this->isPowerSource()) {
            $this->setDamage($this->getDamage() + 8);
            $this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_REDSTONE_TRIGGER, 500);
        } else {
            $this->setDamage($this->getDamage() - 8);
            $this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_REDSTONE_TRIGGER, 600);
        }

        $this->level->setBlock($this, $this);
        $this->updateAroundRedstone($this);
        $this->updateAroundRedstone($this->asVector3()->getSide($this->getFace()));
        return true;
    }

    public function onNearbyBlockChange() : void {
        $block = $this->getSide($this->getFace());
        if(!$block->isSolid() || $block->isTransparent()){
            $this->level->useBreakOn($this);
        }
    }
    
    public function getVariantBitmask() : int {
        return 0;
    }

    public function isSolid() : bool {
        return false;
    }

    public function canPassThrough() : bool {
        return true;
    }
    
    public function getFace() : int {
        $faces = [
            0 => Vector3::SIDE_UP,
            1 => Vector3::SIDE_WEST,
            2 => Vector3::SIDE_EAST,
            3 => Vector3::SIDE_NORTH,
            4 => Vector3::SIDE_SOUTH,
            5 => Vector3::SIDE_DOWN,
            6 => Vector3::SIDE_DOWN,
            7 => Vector3::SIDE_UP
        ];
        return $faces[$this->getDamage() & 0x07];
    }

    public function getStrongPower(int $face) : int {
        if (!$this->isPowerSource()) {
            return 0;
        }
        if ($face == Facing::opposite($this->getFace())) {
            return 15;
        }
        return 0;
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