<?php

namespace redstone\blocks;

use pocketmine\Player;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\Flowable;
use pocketmine\block\Slab;
use pocketmine\block\Stair;

use pocketmine\item\Item;

use pocketmine\math\Vector3;

use redstone\utils\Facing;
use redstone\utils\RedstoneUtils;

use function count;
use function max;

class BlockRedstoneWire extends Flowable implements IRedstone {
    use RedstoneTrait;
    
    protected $id = self::REDSTONE_WIRE;
    protected $itemId = Item::REDSTONE;
    
    public function __construct(int $meta = 0){
        $this->meta = $meta;
    }

    public function getName() : string {
        return "Redstone Wire";
    }
    
    public function getVariantBitmask() : int {
        return 0;
    }

    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool {
        $under = $this->getSide(Facing::DOWN);
        if ($under instanceof Stair) {
            if ($under->getDamage() < 4) {
                return false;
            }
        } elseif ($under instanceof Slab) {
            if ($under->getDamage() < 8) {
                return false;
            }
        } elseif (!$under->isSolid() || $under->isTransparent()) {
            return false;
        }

        $this->getLevel()->setBlock($blockReplace, $this);
        $this->updateAroundDiodeRedstone($this);
        return true;
    }

    public function onBreak(Item $item, Player $player = null) : bool {
        $this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR));
        $this->updateAroundDiodeRedstone($this);
        return true;
    }

    public function onNearbyBlockChange() : void {
        $this->onRedstoneUpdate();

        $under = $this->getSide(Facing::DOWN);
        if ($under instanceof Stair) {
            if ($under->getDamage() >= 4) {
                return;
            }
        } elseif ($under instanceof Slab) {
            if ($under->getDamage() >= 8) {
                return;
            }
        } elseif ($under->isSolid() && !$under->isTransparent()) {
            return;
        }
        $this->level->useBreakOn($this);
    }

    public function getStrongPower(int $face) : int {
        return $this->getWeakPower($face);
    }

    public function getWeakPower(int $face) : int {
        if ($face == Facing::UP) {
            return $this->getDamage();
        }
        if ($this->getSide(Facing::opposite($face)) instanceof BlockRedstoneDiode) {
            return $this->getDamage();
        }
        $direction = Facing::HORIZONTAL;
        $direction = array_diff($direction, array($face, Facing::opposite($face)));
        $direction = array_values($direction);
        for ($i = 0; $i < count($direction); ++$i) {
            $side = $this->getSide($direction[$i]);
            if (RedstoneUtils::isPowerSource($side)) {
                return 0;
            } elseif ($side instanceof BlockRedstoneWire || $side instanceof BlockRedstoneDiode) {
                return 0;
            }
        }
        return $this->getDamage();
    }

    public function isPowerSource() : bool {
        return false;
    }

    public function onRedstoneUpdate() : void {
        $power = 0;

        $direction = Facing::ALL;
        for ($i = 0; $i < count($direction); ++$i) {
            $face = $direction[$i];
            $block = $this->getSide($face);

            if ($block instanceof BlockRedstoneWire) {
                $power = max($power, $block->getDamage() - 1);
            } elseif (RedstoneUtils::isPowerSource($block)) {
                $power = max($power, RedstoneUtils::getWeakPower($block, $face));
            } elseif (RedstoneUtils::isNormalBlock($block)) {
                for ($j = 0; $j < count($direction); ++$j) {
                    $side = $direction[$j];
                    if ($side == Facing::opposite($face)) {
                        continue;
                    }
                    $sideBlock = $block->getSide($side);
                    if (RedstoneUtils::isPowerSource($sideBlock)) {
                        $power = max($power, $sideBlock->getStrongPower($side));
                    }
                }
            } elseif ($block->isTransparent()) {
                if ($face == Facing::UP) {
                    $horizontal = Facing::HORIZONTAL;
                    for ($j = 0; $j < count($horizontal); ++$j) {
                        $side = $horizontal[$j];
                        $sideBlock = $block->getSide($side);
                        if ($sideBlock instanceof BlockRedstoneWire) {
                            $power = max($power, $sideBlock->getDamage() - 1);
                        }
                    }
                } elseif ($face != Facing::DOWN) {
                    $sideBlock = $block->getSide(Facing::DOWN);
                    if ($sideBlock instanceof BlockRedstoneWire) {
                        $power = max($power, $sideBlock->getDamage() - 1);
                    }
                }
            }
        }
        
        if ($this->getDamage() != $power) {
            $this->setDamage($power);
            $this->level->setBlock($this, $this);
            $this->updateAroundDiodeRedstone($this);
        }
    }
}