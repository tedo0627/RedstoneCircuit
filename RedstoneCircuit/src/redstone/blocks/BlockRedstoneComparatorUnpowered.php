<?php

namespace redstone\blocks;

use pocketmine\Player;

use pocketmine\item\Item;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;

use pocketmine\math\Vector3;

use pocketmine\network\mcpe\protocol\LevelEventPacket;

use pocketmine\tile\Container;


use redstone\utils\Facing;

class BlockRedstoneComparatorUnpowered extends BlockRedstoneDiode {

    protected $id = self::UNPOWERED_COMPARATOR;
	protected $itemId = Item::COMPARATOR;

    public function getName() : string
    {
        return "Unpowered Comparator";
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
        $this->updateAroundRedstone($this);
        $direction = Facing::ALL;
        for ($i = 0; $i < count($direction); ++$i) {
            $this->updateAroundRedstone($this->asVector3()->getSide($direction[$i]));
        }
		return true;
    }

	public function onScheduledUpdate() : void {
        if (!$this->isSidePowered($this->asVector3(), $this->getInputFace())) {
            return;
        }

        if ($this->getOutputPower() <= 0) {
            return;
        }
        
        $this->getLevel()->setBlock($this, new BlockRedstoneComparatorPowered($this->getDamage()));
        
        $this->updateAroundRedstone($this);
        $direction = Facing::ALL;
        for ($i = 0; $i < count($direction); ++$i) {
            $this->updateAroundRedstone($this->asVector3()->getSide($direction[$i]));
        }
	}

    public function isComparisonMode() : bool {
        return $this->getDamage() < 4;
    }

    public function isSubtractionMode() : bool {
        return $this->getDamage() >= 4;
    }

    public function getOutputPower() : int {
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

        if ($this->isComparisonMode()) {
            if ($power >= $sidePower) {
                return $power;
            }
        } else {
            if ($power - $sidePower > 0) {
                return $power - $sidePower;
            }
        }
        return 0;
    }

    public function onRedstoneUpdate() : void {
        if ($this->isSidePowered($this->asVector3(), $this->getInputFace())) {
            $this->level->scheduleDelayedBlockUpdate($this, 2);
        }
	}
}