<?php

namespace redstone\listeners;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\math\Vector3;

use pocketmine\network\mcpe\protocol\CommandBlockUpdatePacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;


use redstone\Main;

use redstone\blockEntities\BlockEntityCommandBlock;

use redstone\blocks\BlockNote;

class EventListener implements Listener {
	
    public function onDataPacketReceive(DataPacketReceiveEvent $event) : void {
        $packet = $event->getPacket();
	}
}
