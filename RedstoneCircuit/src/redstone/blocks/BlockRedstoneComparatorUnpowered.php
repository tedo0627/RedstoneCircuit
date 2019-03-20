<?php

namespace redstone\blocks;

use pocketmine\Player;

use pocketmine\item\Item;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;

use pocketmine\math\Vector3;

use pocketmine\network\mcpe\protocol\LevelEventPacket;

use pocketmine\tile\Tile;
use pocketmine\tile\Container;


use redstone\blockEntities\BlockEntityRedstoneComparator;

use redstone\utils\Facing;

class BlockRedstoneComparatorUnpowered extends BlockRedstoneDiode {

    protected $id = self::UNPOWERED_COMPARATOR;
    protected $itemId = Item::COMPARATOR;

    public function getName() : string {
        return "Unpowered Comparator";
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
        $this->updateAroundDiodeRedstone($this);
        return true;
    }
    
    public function onActivate(Item $item, Player $player = null) : bool {
        if ($this->getDamage() >= 4) {
            $this->setDamage($this->getDamage() - 4);
            $this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_REDSTONE_TRIGGER, 500);
        } else {
            $this->setDamage($this->getDamage() + 4);
            $this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_REDSTONE_TRIGGER, 550);
        }
        $this->level->setBlock($this, $this);
        $this->onRedstoneUpdate();
        return true;
    }

    public function onScheduledUpdate() : void {
        $this->recalculateoutputPower();
        if (!$this->isSidePowered($this->asVector3(), $this->getInputFace())) {
            return;
        }

        if ($this->getOutputPower() <= 0) {
            return;
        }
        
        $this->getLevel()->setBlock($this, new BlockRedstoneComparatorPowered($this->getDamage()));
        $this->updateAroundDiodeRedstone($this);
    }
    
    public function getBlockEntity() : BlockEntityRedstoneComparator {
        $tile = $this->getLevel()->getTile($this);
        $comparator = null;
        if($tile instanceof BlockEntityRedstoneComparator){
            $comparator = $tile;
        }else{
            $comparator = Tile::createTile("BlockEntityRedstoneComparator", $this->getLevel(), BlockEntityRedstoneComparator::createNBT($this));
        }
        return $comparator;
    }

    public function isComparisonMode() : bool {
        return $this->getDamage() < 4;
    }

    public function isSubtractionMode() : bool {
        return $this->getDamage() >= 4;
    }

    public function getOutputPower() : int {
        return $this->getBlockEntity()->getOutputSignal();
    }

    protected function recalculateoutputPower() : void {
        $power = $this->getRedstonePower($this->asVector3()->getSide($this->getInputFace()), $this->getInputFace());

        /* TODO
        $tile = $this->level->getTile($this->asVector3()->getSide($this->getInputFace()));
        if ($tile instanceof Container) {
            $inventory = $tile->getInventory();
        }
        */

        $sidePower = 0;
        $face = Facing::rotate($this->getInputFace(), Facing::AXIS_Y, false);
        $block = $this->getSide($face);
        if ($block instanceof BlockRedstoneDiode || $block instanceof BlockRedstoneWire) {
            $sidePower = max($sidePower, $block->getWeakPower($face));
        }

        $face = Facing::opposite($face);
        $block = $this->getSide($face);
        if ($block instanceof BlockRedstoneDiode || $block instanceof BlockRedstoneWire) {
            $sidePower = max($sidePower, $block->getWeakPower($face));
        }

        $p = 0;
        if ($this->isComparisonMode()) {
            if ($power >= $sidePower) {
                $p = $power;
            }
        } else {
            if ($power - $sidePower > 0) {
                $p = $power - $sidePower;
            }
        }
        
        $this->getBlockEntity()->setOutputSignal($p);
    }

    public function onRedstoneUpdate() : void {
        if ($this->isSidePowered($this->asVector3(), $this->getInputFace())) {
            $this->level->scheduleDelayedBlockUpdate($this, 2);
        }
    }
}