<?php

namespace redstone\blockEntities;

use pocketmine\block\Block;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\tile\Container;
use pocketmine\tile\ItemFrame;
use pocketmine\tile\Tile;

use redstone\blocks\BlockRedstoneComparatorPowered;
use redstone\blocks\BlockRedstoneComparatorUnpowered;
use redstone\blocks\BlockRedstoneDiode;
use redstone\blocks\BlockRedstoneWire;

use redstone\utils\Facing;
use redstone\utils\RedstoneUtils;

class BlockEntityRedstoneComparator extends Tile {

    protected $outputSignal = 0;

    protected function readSaveData(CompoundTag $nbt) : void {
        if ($nbt->hasTag("outputSignal")) {
            $this->outputSignal = $nbt->getInt("outputSignal");
        }

        $this->scheduleUpdate();
    }

    protected function writeSaveData(CompoundTag $nbt) : void {
        $nbt->setInt("outputSignal", $this->outputSignal);
    }

    public function onUpdate() : bool {
        if ($this->isClosed()) {
            return false;
        }

        if (!$this->hasSideUtility()) {
            return true;
        }

        $power = $this->recalculateOutputPower();
        $this->setOutputSignal($power);

        $block = $this->getBlock();
        if ($block->getId() == Block::UNPOWERED_COMPARATOR && $power > 0) {
            $this->getLevel()->setBlock($this, new BlockRedstoneComparatorPowered($block->getDamage()));
        } else if ($block->getId() == Block::POWERED_COMPARATOR && $power == 0) {
            $this->getLevel()->setBlock($this, new BlockRedstoneComparatorUnpowered($block->getDamage()));
        }
        $this->getBlock()->updateAroundDiodeRedstone($this);
        return true;
    }

    public function getOutputSignal() : int {
        return $this->outputSignal;
    }

    public function setOutputSignal(int $signal) : void {
        $this->outputSignal = $signal;
    }

    protected function hasSideUtility() : bool {
        $block = $this->getBlock();
        $sideBlock = $block->getSide($block->getInputFace());
        $tile = $this->getLevel()->getTile($sideBlock);
        if ($tile instanceof Container) {
            return true;
        }

        $id = $sideBlock->getId();
        if ($id == 92 || $id == 199) {
            return true;
        }

        if (RedstoneUtils::isNormalBlock($sideBlock)) {
            $sideBlock = $sideBlock->getSide($block->getInputFace());
            $tile = $this->getLevel()->getTile($sideBlock);
            if ($tile instanceof Container) {
                return true;
            }

            $id = $sideBlock->getId();
            if ($id == 92 || $id == 199) {
                return true;
            }
        }

        return false;
    }

    public function recalculateOutputPower() : int {
        $block = $this->getBlock();
        $power = $block->getRedstonePower($this->getSide($block->getInputFace()), $block->getInputFace());// HACK: Method 'getRedstonePower' not found in \pocketmine\block\Block
        $power = max($power, $this->recalculateSideUtilityPower());

        $sidePower = 0;
        $face = Facing::rotate($block->getInputFace(), Facing::AXIS_Y, false);
        $side = $block->getSide($face);
        if ($side instanceof BlockRedstoneDiode || $side instanceof BlockRedstoneWire) {
            $sidePower = max($sidePower, $side->getWeakPower($face));
        }

        $face = Facing::opposite($face);
        $side = $block->getSide($face);
        if ($side instanceof BlockRedstoneDiode || $side instanceof BlockRedstoneWire) {
            $sidePower = max($sidePower, $side->getWeakPower($face));
        }

        $p = 0;
        if ($block->isComparisonMode()) {
            if ($power >= $sidePower) {
                $p = $power;
            }
        } else {
            if ($power - $sidePower > 0) {
                $p = $power - $sidePower;
            }
        }

        return $p;
    }

    protected function recalculateSideUtilityPower() : int {
        $block = $this->getBlock();
        $sideBlock = $block->getSide($block->getInputFace());
        $power = $this->recalculateUtilityPower($sideBlock);
        if ($power > 0) {
            return $power;
        }

        if (RedstoneUtils::isNormalBlock($sideBlock)) {
            $sideBlock = $sideBlock->getSide($block->getInputFace());
            $power = $this->recalculateUtilityPower($sideBlock);
        }

        return $power;
    }

    protected function recalculateUtilityPower(Block $block) : int {
        $tile = $this->getLevel()->getTile($block);
        if ($tile instanceof Container) {
            $inventory = $tile->getInventory();
            if (count($inventory->getContents()) == 0) {
                return 0;
            }

            $stack = 0;
            for ($i = 0; $i < $inventory->getSize(); ++$i) {
                $item = $inventory->getItem($i);
                if ($item->getId() == 0) {
                    continue;
                }

                $stack += $item->getCount() / $item->getMaxStackSize();
            }

            return 1 + ($stack / $inventory->getSize()) * 14;
        }

        if ($tile instanceof ItemFrame) {
            if ($tile->getItem()->getId() == 0) {
                return 0;
            }
            return $tile->getItemRotation() + 1;
        }

        if ($block->getId() == 92) {
            return (7 - $block->getDamage()) * 2;
        }
        return 0;
    }
}