<?php

namespace redstone\blocks;

class BlockCommandRepeating extends BlockCommand {

    protected $id = self::REPEATING_COMMAND_BLOCK;
    
    public function getName() : string {
        return "Command Block Repeating";
    }

    public function getMode() : int {
        return 1;
    }
}