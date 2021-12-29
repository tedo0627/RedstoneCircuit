<?php

namespace tedo0627\redstonecircuit\block\transmission;

use pocketmine\block\Block;
use pocketmine\block\RedstoneWire;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use tedo0627\redstonecircuit\block\BlockPowerHelper;
use tedo0627\redstonecircuit\block\BlockUpdateHelper;
use tedo0627\redstonecircuit\block\FlowablePlaceHelper;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;

class BlockRedstoneWire extends RedstoneWire implements IRedstoneComponent {
    use RedstoneComponentTrait;

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool {
        if (!FlowablePlaceHelper::check($this, Facing::DOWN)) return false;

        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

    public function onPostPlace(): void {
        $this->calculatePower();
    }

    public function onBreak(Item $item, ?Player $player = null): bool {
        $bool = parent::onBreak($item, $player);
        BlockUpdateHelper::updateAroundStrongRedstone($this);
        return $bool;
    }

    public function onNearbyBlockChange(): void {
        if (FlowablePlaceHelper::check($this, Facing::DOWN)) {
            $this->calculatePower();
        } else {
            $this->getPosition()->world->useBreakOn($this->getPosition());
        }
    }

    public function getWeakPower(int $face): int {
        return $this->signalStrength;
    }

    public function onRedstoneUpdate(): void {
        $this->calculatePower();
    }

    private function calculatePower(): void {
        $power = 0;
        for ($face = 0; $face < 6; $face++) {
            $block = $this->getSide($face);
            if ($block instanceof BlockRedstoneWire) {
                $power = max($power, $block->getWeakPower($face) - 1);
                continue;
            }

            if (BlockPowerHelper::isPowerSource($block)) {
                $power = max($power, BlockPowerHelper::getWeakPower($block, $face));
                continue;
            }

            if (BlockPowerHelper::isNormalBlock($block)) {
                for ($sideFace = 0; $sideFace < 6; $sideFace++) {
                    if ($sideFace == Facing::opposite($face)) continue;

                    $power = max($power, BlockPowerHelper::getStrongPower($block->getSide($sideFace), $sideFace));
                }
            }

            if ($face == Facing::DOWN) continue;

            if ($block->isTransparent()) {
                if ($face == Facing::UP) {
                    for ($sideFace = 2; $sideFace < 6; $sideFace++) {
                        $sideBlock = $block->getSide($sideFace);
                        if (!$sideBlock instanceof BlockRedstoneWire) continue;

                        $power = max($power, $sideBlock->getWeakPower($sideFace) - 1);
                    }
                    continue;
                }

                $sideBlock = $block->getSide(Facing::DOWN);
                if (!$sideBlock instanceof BlockRedstoneWire) continue;

                $power = max($power, $sideBlock->getWeakPower(Facing::DOWN) - 1);
            }
        }

        if ($this->getWeakPower(0) == $power) return;

        $this->setOutputSignalStrength($power);
        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
        BlockUpdateHelper::updateAroundStrongRedstone($this);
    }
}