<?php

namespace redstone\blocks;

class BlockPistonSticky extends BlockPiston {
    
    protected $id = self::STICKY_PISTON;

    public function getName() : string {
        return "Sticky Piston";
    }

    public function isSticky() : bool {
        return true;
    }
}