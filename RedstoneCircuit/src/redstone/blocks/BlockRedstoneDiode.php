<?php

namespace redstone\blocks;

use pocketmine\Player;

use pocketmine\item\Item;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\Flowable;

use pocketmine\math\Vector3;

use redstone\utils\Facing;

abstract class BlockRedstoneDiode extends Flowable implements IRedstone {
    use RedstoneTrait;

    public function __construct(int $meta = 0){
        $this->meta = $meta;
    }
    
    public function getVariantBitmask() : int {
        return 0;
    }
    
    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool {
        $under = $this->getSide(Facing::DOWN);
        if (!$under->isSolid() || $under->isTransparent()) {
            return false;
        }

        $faces = [
            0 => 1,
            1 => 2,
            2 => 3,
            3 => 0
        ];
        $this->setDamage($faces[$player instanceof Player ? $player->getDirection() : 0]);
        $this->level->setBlock($this, $this);
        $this->onRedstoneUpdate();
        $this->updateAroundDiodeRedstone($this);
        return true;
    }
    
    public function onBreak(Item $item, Player $player = null) : bool {
        $this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR));
        $this->updateAroundDiodeRedstone($this);
        return true;
    }

    public function getInputFace() : int {
        $faces = [
            0 => Facing::SOUTH,
            1 => Facing::WEST,
            2 => Facing::NORTH,
            3 => Facing::EAST
        ];
        return $faces[$this->getDamage() % 4];
    }

    public function getOutputFace() : int {
        return Facing::opposite($this->getInputFace());
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
    }
}