<?php

namespace redstone\blockEntities;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\tile\Tile;

use redstone\blocks\BlockDaylightDetector;
use redstone\blocks\BlockDaylightDetectorInverted;

class BlockEntityDaylightDetector extends Tile {

    protected function readSaveData(CompoundTag $nbt) : void {
        $this->scheduleUpdate();
    }

    protected function writeSaveData(CompoundTag $nbt) : void {
    }

    public function onUpdate() : bool{
        if ($this->isClosed()) {
            return false;
        }

        $time = $this->getLevel()->getTime();
        if ($time % 20 != 0) {
            return true;
        }

        $block = $this->getBlock();
        if (!($block instanceof BlockDaylightDetector)) {
            return false;
        }
        $block->updatePower();
        return true;
    }

    public function getPower() {
        $time = $this->getLevel()->getTime();
        $power = $this->getTimePower($time);
        $power -= (15 - $this->getLevel()->getSkyLightReduction() - $this->getLevel()->getRealBlockSkyLightAt($this->getX(), $this->getY(), $this->getZ()));

        if ($power < 0) {
            $power = 0;
        }

        if ($this->getBlock() instanceof BlockDaylightDetectorInverted) {
            $power = 15 - $power;
        }

        return $power;
    }

    private function getTimePower(int $time) : int {
        $time = $time % 24000;
        if ($time >= 23960) {
            $power = 7;
        } else if ($time >= 23780) {
            $power = 6;
        } else if ($time >= 23540) {
            $power = 5;
        } else if ($time >= 23300) {
            $power = 4;
        } else if ($time >= 23080) {
            $power = 3;
        } else if ($time >= 22800) {
            $power = 2;
        } else if ($time >= 22340) {
            $power = 1;
        } else if ($time >= 13680) {
            $power = 0;
        } else if ($time >= 13220) {
            $power = 1;
        } else if ($time >= 12940) {
            $power = 2;
        } else if ($time >= 12720) {
            $power = 3;
        } else if ($time >= 12480) {
            $power = 4;
        } else if ($time >= 12240) {
            $power = 5;
        } else if ($time >= 12040) {
            $power = 6;
        } else if ($time >= 11840) {
            $power = 7;
        } else if ($time >= 11480) {
            $power = 8;
        } else if ($time >= 11080) {
            $power = 9;
        } else if ($time >= 10640) {
            $power = 10;
        } else if ($time >= 10140) {
            $power = 11;
        } else if ($time >= 9560) {
            $power = 12;
        } else if ($time >= 8840) {
            $power = 13;
        } else if ($time >= 7720) {
            $power = 14;
        } else if ($time >= 4300) {
            $power = 15;
        } else if ($time >= 3180) {
            $power = 14;
        } else if ($time >= 2460) {
            $power = 13;
        } else if ($time >= 1880) {
            $power = 12;
        } else if ($time >= 1380) {
            $power = 11;
        } else if ($time >= 940) {
            $power = 10;
        } else if ($time >= 540) {
            $power = 9;
        } else if ($time >= 180) {
            $power = 8;
        } else {
            $power = 7;
        }
        return $power;
    }
}