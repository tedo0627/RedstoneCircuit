<?php

namespace tedo0627\redstonecircuit;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds as Ids;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\plugin\PluginBase;
use tedo0627\redstonecircuit\block\mechanism\BlockRedstoneLamp;
use tedo0627\redstonecircuit\block\power\BlockLever;
use tedo0627\redstonecircuit\block\power\BlockRedstone;
use tedo0627\redstonecircuit\block\transmission\BlockRedstoneComparator;
use tedo0627\redstonecircuit\block\transmission\BlockRedstoneRepeater;
use tedo0627\redstonecircuit\block\transmission\BlockRedstoneWire;
use tedo0627\redstonecircuit\item\ItemRedstone;

class RedstoneCircuit extends PluginBase {

    public function onLoad(): void {
        $this->registerBlock(Ids::REDSTONE_WIRE, fn($bid, $name, $info) => new BlockRedstoneWire($bid, $name, $info));
        $this->registerBlock(Ids::REDSTONE_BLOCK, fn($bid, $name, $info) => new BlockRedstone($bid, $name, $info));
        $this->registerBlock(Ids::REDSTONE_LAMP, fn($bid, $name, $info) => new BlockRedstoneLamp($bid, $name, $info));
        $this->registerBlock(Ids::LEVER, fn($bid, $name, $info) => new BlockLever($bid, $name, $info));
        $this->registerBlock(Ids::UNPOWERED_REPEATER, fn($bid, $name, $info) => new BlockRedstoneRepeater($bid, $name, $info));
        $this->registerBlock(Ids::UNPOWERED_COMPARATOR, fn($bid, $name, $info) => new BlockRedstoneComparator($bid, $name, $info));
        ItemFactory::getInstance()->register(new ItemRedstone(new ItemIdentifier(ItemIds::REDSTONE, 0), "Redstone"), true);
    }

    private function registerBlock(int $id, $callback): void {
        $factory = BlockFactory::getInstance();
        $oldBlock = $factory->get($id, 0);
        $block = $callback($oldBlock->getIdInfo(), $oldBlock->getName(), $oldBlock->getBreakInfo());
        $factory->register($block, true);
    }
}