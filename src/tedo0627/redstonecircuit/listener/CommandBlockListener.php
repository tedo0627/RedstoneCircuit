<?php

namespace tedo0627\redstonecircuit\listener;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\CommandBlockUpdatePacket;
use pocketmine\Server;
use tedo0627\redstonecircuit\block\mechanism\BlockCommand;

class CommandBlockListener implements Listener {

    public function onDataPacketReceive(DataPacketReceiveEvent $event): void {
        $packet = $event->getPacket();
        if (!$packet instanceof CommandBlockUpdatePacket) return;

        $player = $event->getOrigin()->getPlayer();
        if ($player === null) return;
        if (!$packet->isBlock) return;

        $server = Server::getInstance();
        if (!$server->isOp($player->getName()) || !$player->isCreative()) return;

        $pos = $packet->blockPosition;
        $world = $player->getWorld();
        $block = $world->getBlockAt($pos->getX(), $pos->getY(), $pos->getZ());
        if (!$block instanceof BlockCommand) return;

        $block->setCommandBlockMode($packet->commandBlockMode);
        $block->setAuto(!$packet->isRedstoneMode);
        $block->setConditionalMode($packet->isConditional);
        $block->setCommand($packet->command);
        $block->setLastOutput($packet->lastOutput);
        $block->setCustomName($packet->name);
        $block->setTickDelay($packet->tickDelay);
        $block->setExecuteOnFirstTick($packet->executeOnFirstTick);
        $block->setTick(-1);
        $pos = $block->getPosition();
        $world->setBlock($pos, $block);
        $world->scheduleDelayedBlockUpdate($pos, 1);
    }
}