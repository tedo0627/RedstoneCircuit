<?php

namespace tedo0627\redstonecircuit\listener;

use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\Listener;
use tedo0627\redstonecircuit\block\power\BlockTarget;

class TargetBlockListener implements Listener {

    public function onProjectileHitBlock(ProjectileHitBlockEvent $event): void {
        $block = $event->getBlockHit();
        if (!$block instanceof BlockTarget) return;

        $result = $event->getRayTraceResult();
        $block->hit($event->getEntity(), $result->getHitFace(), $result->getHitVector());
    }
}