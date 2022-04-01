<?php

namespace tedo0627\redstonecircuit\event;

use pocketmine\block\Block;
use tedo0627\redstonecircuit\block\mechanism\BlockPiston;

class BlockPistonExtendEvent extends BlockPistonEvent {

    /** @var Block[] */
    private array $moveBlocks;
    /** @var Block[] */
    private array $breakBlocks;

    /**
     * @param BlockPiston $piston
     * @param Block[] $moveBlocks
     * @param Block[] $breakBlocks
     */
    public function __construct(BlockPiston $piston, array $moveBlocks, array $breakBlocks) {
        parent::__construct($piston);

        $this->moveBlocks = $moveBlocks;
        $this->breakBlocks = $breakBlocks;
    }

    /** @return Block[] */
    public function getMoveBlocks(): array {
        return $this->moveBlocks;
    }

    /** @return Block[] */
    public function getBreakBlocks(): array {
        return $this->breakBlocks;
    }
}