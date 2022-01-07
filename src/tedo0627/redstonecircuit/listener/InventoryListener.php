<?php

namespace tedo0627\redstonecircuit\listener;

use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\inventory\Inventory;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use tedo0627\redstonecircuit\block\inventory\DropperInventory;

class InventoryListener implements Listener {

    private ?Inventory $lastInventory = null;

    public function onInventoryOpen(InventoryOpenEvent $event) {
        $inventory = $event->getInventory();
        if (!$inventory instanceof DropperInventory) {
            $this->lastInventory = null;
            return;
        }

        $this->lastInventory = $inventory;
    }

    public function onDataPacketSend(DataPacketSendEvent $event) {
        $packets = $event->getPackets();
        for ($i = 0; $i < count($packets); $i++) {
            $packet = $packets[$i];
            if (!$packet instanceof ContainerOpenPacket) continue;

            $inventory = $this->lastInventory;
            $this->lastInventory = null;
            if (!$inventory instanceof DropperInventory) return;

            $sessions = $event->getTargets();
            for ($j = 0; $j < count($sessions); $j++) {
                $session = $sessions[$j];
                $player = $session->getPlayer();
                if ($player === null) continue;

                $packet->windowType = WindowTypes::DROPPER;
            }
        }
    }
}