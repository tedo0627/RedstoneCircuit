<?php

namespace tedo0627\redstonecircuit\event;

use pocketmine\event\block\BlockEvent;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use tedo0627\redstonecircuit\block\mechanism\BlockPiston;

class BlockPistonEvent extends BlockEvent implements Cancellable {
    use CancellableTrait;

    private BlockPiston $piston;

    public function __construct(BlockPiston $piston) {
        parent::__construct($piston);

        $this->piston = $piston;
    }

    public function getPiston(): BlockPiston {
        return $this->piston;
    }

    public function isSticky(): bool {
        return $this->piston->isSticky();
    }
}