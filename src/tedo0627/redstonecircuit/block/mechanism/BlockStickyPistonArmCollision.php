<?php

namespace tedo0627\redstonecircuit\block\mechanism;

class BlockStickyPistonArmCollision extends BlockPistonArmCollision {

    public function isSticky(): bool {
        return true;
    }
}