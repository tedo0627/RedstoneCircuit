<?php

namespace redstone\blocks;

use pocketmine\Player;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockToolType;
use pocketmine\block\Solid;

use pocketmine\item\Item;
use pocketmine\item\TieredTool;

use pocketmine\math\Vector3;

use pocketmine\tile\Tile;


use redstone\blockEntities\BlockEntityObserver;

use redstone\utils\Facing;

class BlockObserver extends Solid implements IRedstone {
    use RedstoneTrait;

    protected $id = self::OBSERVER;
    
    public function __construct(int $meta = 0){
        $this->meta = $meta;
    }

    public function getName() : string {
        return "Observer";
    }

    public function getHardness() : float {
        return 3.5;
    }

    public function getToolType() : int{
        return BlockToolType::TYPE_PICKAXE;
    }

    public function getToolHarvestLevel() : int{
        return TieredTool::TIER_WOODEN;
    }

    public function getVariantBitmask() : int {
        return 0;
    }

    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool {
        $damage = 0;
        if($player !== null) {
            $faces = [5, 3, 4, 2];
            $damage = $faces[$player->getDirection()];
            if ($player->getPitch() > 45) {
                $damage = 0;
            } else if ($player->getPitch() < -45) {
                $damage = 1;
            }
        }

        $this->setDamage($damage);
        $this->getLevel()->setBlock($this, $this, true, true);

        $runtimeId = $this->getSide($this->getInputFace())->getRuntimeId();
        $nbt = BlockEntityObserver::createNBT($this);
        $nbt->setInt("runtimeId", $runtimeId);

        Tile::createTile("BlockEntityObserver", $this->getLevel(), $nbt);
        return true;
    }

    public function onBreak(Item $item, Player $player = null) : bool {
        $this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR));
        $this->updateAroundDiodeRedstone($this);
        return true;
    }

    public function onScheduledUpdate() : void {
        $this->setDamage($this->getDamage() ^ 0x08);
        $this->getLevel()->setBlock($this, $this);
        $this->updateAroundDiodeRedstone($this);

        if ($this->isActivated()) {
            $this->level->scheduleDelayedBlockUpdate($this, 2);
        }
    }

	public function onNearbyBlockChange() : void{
        $runtimeId = $this->getSide($this->getInputFace())->getRuntimeId();
        $observer = $this->getBlockEntity();
        if ($observer->getSideRuntimeId() == $runtimeId) {
            return;
        }
        $observer->setSideRuntimeId($runtimeId);
        $this->level->scheduleDelayedBlockUpdate($this, 2);
	}

    public function getBlockEntity() : BlockEntityObserver {
        $tile = $this->getLevel()->getTile($this);
        $observer = null;
        if($tile instanceof BlockEntityObserver){
            $observer = $tile;
        }else{
            $observer = Tile::createTile("BlockEntityObserver", $this->getLevel(), BlockEntityObserver::createNBT($this));
        }
        return $observer;
    }

    public function getInputFace() : int {
        $damage = $this->getDamage();
        if ($this->isActivated()) {
            $damage -= 8;
        }
        return $damage;
    }

    public function getOutputFace() : int {
        return Facing::opposite($this->getInputFace());
    }

    public function isActivated() : bool {
        return $this->getDamage() >= 8;
    }

    public function getStrongPower(int $face) : int {
        if ($this->isActivated() && $this->getInputFace() == $face) {
            return 15;
        }
        return 0;
    }

    public function getWeakPower(int $face) : int {
        if ($this->isActivated() && $this->getInputFace() == $face) {
            return 15;
        }
        return 0;
    }

    public function isPowerSource() : bool {
        return $this->isActivated();
    }

    public function onRedstoneUpdate() : void {
    }
}