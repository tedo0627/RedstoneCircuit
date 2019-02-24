<?php

namespace redstone\blocks;

class BlockCommandChain extends BlockCommand {

    protected $id = self::CHAIN_COMMAND_BLOCK;
    
    public function getName() : string {
        return "Command Block Chain";
    }
    
    public function getMode() : int {
        return 2;
    }
}