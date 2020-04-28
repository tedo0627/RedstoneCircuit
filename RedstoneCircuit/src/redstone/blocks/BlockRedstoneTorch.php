<?php

namespace redstone\blocks;

use pocketmine\Player;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\Torch;

use pocketmine\item\Item;

use pocketmine\math\Vector3;

use redstone\utils\Facing;

class BlockRedstoneTorch extends Torch implements IRedstone {
    use RedstoneTrait;
    
    protected $id = self::REDSTONE_TORCH;

    public function __construct(int $meta = 0){
        $this->meta = $meta;
    }

    public function getName() : string {
        return "Redstone Torch";
    }
    
    public function getVariantBitmask() : int {
        return 0;
    }

    public function getLightLevel() : int {
        return 7;
    }

    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool {
        $below = $this->getSide(Vector3::SIDE_DOWN);

        if(!$blockClicked->isTransparent() and $face !== Vector3::SIDE_DOWN){
            $faces = [
                Vector3::SIDE_UP => 5,
                Vector3::SIDE_NORTH => 4,
                Vector3::SIDE_SOUTH => 3,
                Vector3::SIDE_WEST => 2,
                Vector3::SIDE_EAST => 1
            ];
            $this->meta = $faces[$face];
            $this->getLevel()->setBlock($blockReplace, $this, true, true);
            $this->updateAroundDiodeRedstone($this);

            return true;
        }elseif(!$below->isTransparent() or $below->getId() === self::FENCE or $below->getId() === self::COBBLESTONE_WALL){
            $this->meta = 0;
            $this->getLevel()->setBlock($blockReplace, $this, true, true);
            $this->updateAroundDiodeRedstone($this);
            return true;
        }

        return false;
    }
    
    public function onBreak(Item $item, Player $player = null) : bool {
        $this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR));
        $this->updateAroundDiodeRedstone($this);
        return true;
    }

    public function onScheduledUpdate() : void {
        if ($this->isSidePowered($this, $this->getFace())) {
            $this->getLevel()->setBlock($this, new BlockRedstoneTorchUnlit($this->getDamage()));
            $this->updateAroundDiodeRedstone($this);
        }
    }
    
    public function getFace() : int {
        $face = [
            1 => Facing::WEST,
            2 => Facing::EAST,
            3 => Facing::NORTH,
            4 => Facing::SOUTH,
            5 => Facing::DOWN,
        ];
        if (isset($face[$this->getDamage()])) {
            return $face[$this->getDamage()];
        }
        return Facing::DOWN;
    }

    public function getStrongPower(int $face) : int {
        if ($face == Facing::DOWN) {
            return 15;
        }
        return 0;
    }

    public function getWeakPower(int $face) : int {
        if ($face == Facing::opposite($this->getFace())) {
            return 0;
        }
        return 15;
    }

    public function isPowerSource() : bool {
        return true;
    }

    public function onRedstoneUpdate() : void {
        if ($this->isSidePowered($this, $this->getFace())) {
            $this->level->scheduleDelayedBlockUpdate($this, 2);
        }
    }
}