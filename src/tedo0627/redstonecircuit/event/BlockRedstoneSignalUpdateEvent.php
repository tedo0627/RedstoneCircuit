<?php

namespace tedo0627\redstonecircuit\event;

use pocketmine\block\Block;
use pocketmine\event\block\BlockEvent;

class BlockRedstoneSignalUpdateEvent extends BlockEvent {

    private int $newSignal;
    private int $oldSignal;

    public function __construct(Block $block, int $newSignal, int $oldSignal) {
        parent::__construct($block);

        $this->newSignal = $newSignal;
        $this->oldSignal = $oldSignal;
    }

    public function getNewSignal(): int {
        return $this->newSignal;
    }

    public function setNewSignal(int $signal): void {
        $this->newSignal = $signal;
    }

    public function getOldSignal(): int {
        return $this->oldSignal;
    }
}