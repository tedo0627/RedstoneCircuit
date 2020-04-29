<?php

namespace redstone\listeners;

use pocketmine\event\Listener;

use pocketmine\event\level\LevelLoadEvent;
use pocketmine\event\level\LevelSaveEvent;

use redstone\Main;

class ScheduledBlockUpdateListener implements Listener {

    public function onLevelLoad(LevelLoadEvent $event) : void {
        Main::getInstance()->getScheduledBlockUpdateLoader()->loadLevel($event->getLevel());
    }

    public function onLevelSave(LevelSaveEvent $event) : void {
        Main::getInstance()->getScheduledBlockUpdateLoader()->saveLevel($event->getLevel());
    }
}