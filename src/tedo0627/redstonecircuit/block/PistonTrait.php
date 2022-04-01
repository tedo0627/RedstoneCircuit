<?php

namespace tedo0627\redstonecircuit\block;

use pocketmine\block\Block;

trait PistonTrait {

    protected float $progress = 0;
    protected float $lastProgress = 0;

    protected int $state = 0;
    protected int $newState = 0;

    /** @var int[] */
    protected array $breakBlocks = [];
    /** @var int[] */
    protected array $attachedBlocks = [];

    public function getProgress(): float {
        return $this->progress;
    }

    public function setProgress(float $progress): void {
        $this->progress = $progress;
    }

    public function getLastProgress(): float {
        return $this->lastProgress;
    }

    public function setLastProgress(float $progress): void {
        $this->lastProgress = $progress;
    }

    public function getState(): int {
        return $this->state;
    }

    public function setState(int $state): void {
        $this->state = $state;
    }

    public function getNewState(): int {
        return $this->newState;
    }

    public function setNewState(int $state): void {
        $this->newState = $state;
    }

    /** @return int[] */
    public function getBreakBlocks(): array {
        return $this->breakBlocks;
    }

    /** @param int[] $breakBlocks */
    public function setBreakBlocks(array $breakBlocks): void {
        $this->breakBlocks = $breakBlocks;
    }

    public function addBreakBlock(Block $block): void {
        $pos = $block->getPosition();
        $this->breakBlocks[] = $pos->getFloorX();
        $this->breakBlocks[] = $pos->getFloorY();
        $this->breakBlocks[] = $pos->getFloorZ();
    }

    /** @return int[] */
    public function getAttachedBlocks(): array {
        return $this->attachedBlocks;
    }

    /** @param int[] $attachedBlocks */
    public function setAttachedBlocks(array $attachedBlocks): void {
        $this->attachedBlocks = $attachedBlocks;
    }

    public function addAttachedBlock(Block $block): void {
        $pos = $block->getPosition();
        $this->attachedBlocks[] = $pos->getFloorX();
        $this->attachedBlocks[] = $pos->getFloorY();
        $this->attachedBlocks[] = $pos->getFloorZ();
    }
}