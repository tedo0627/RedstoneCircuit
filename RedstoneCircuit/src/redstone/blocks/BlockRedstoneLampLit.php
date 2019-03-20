<?php

namespace redstone\blocks;

class BlockRedstoneLampLit extends BlockRedstoneLamp {
    
    protected $id = self::LIT_REDSTONE_LAMP;

    public function getName() : string {
        return "Lit Redstone Lamp";
    }

    public function getLightLevel() : int {
        return 15;
    }

    public function onRedstoneUpdate() : void {
        if (!$this->isBlockPowered($this)) {
            $this->level->scheduleDelayedBlockUpdate($this, 8);
        }
    }

    public function onScheduledUpdate() : void {
        if (!$this->isBlockPowered($this)) {
            $this->getLevel()->setBlock($this, new BlockRedstoneLamp());
        }
    }
}