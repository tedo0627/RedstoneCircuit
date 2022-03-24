<?php

namespace tedo0627\redstonecircuit\block;

/**
 * BlockEntity is load, schedule update is called, and therefore, a trait to avoid that update
 */
trait BlockEntityInitializeTrait {

    protected bool $initialized = false;

    public function isInitialized(): bool {
        return $this->initialized;
    }

    public function setInitialized(bool $initialize): void {
        $this->initialized = $initialize;
    }
}