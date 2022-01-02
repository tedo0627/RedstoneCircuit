<?php

namespace tedo0627\redstonecircuit;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds as Ids;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\plugin\PluginBase;
use tedo0627\redstonecircuit\block\mechanism\BlockIronDoor;
use tedo0627\redstonecircuit\block\mechanism\BlockRedstoneLamp;
use tedo0627\redstonecircuit\block\mechanism\BlockWoodenDoor;
use tedo0627\redstonecircuit\block\power\BlockLever;
use tedo0627\redstonecircuit\block\power\BlockRedstone;
use tedo0627\redstonecircuit\block\power\BlockRedstoneTorch;
use tedo0627\redstonecircuit\block\power\BlockStoneButton;
use tedo0627\redstonecircuit\block\power\BlockStonePressurePlate;
use tedo0627\redstonecircuit\block\power\BlockWeightedPressurePlateHeavy;
use tedo0627\redstonecircuit\block\power\BlockWeightedPressurePlateLight;
use tedo0627\redstonecircuit\block\power\BlockWoodenButton;
use tedo0627\redstonecircuit\block\power\BlockWoodenPressurePlate;
use tedo0627\redstonecircuit\block\transmission\BlockRedstoneComparator;
use tedo0627\redstonecircuit\block\transmission\BlockRedstoneRepeater;
use tedo0627\redstonecircuit\block\transmission\BlockRedstoneWire;
use tedo0627\redstonecircuit\item\ItemRedstone;

class RedstoneCircuit extends PluginBase {

    public function onLoad(): void {
        // mechanism
        $this->registerBlock(Ids::IRON_DOOR_BLOCK, fn($bid, $name, $info) => new BlockIronDoor($bid, $name, $info));
        $this->registerBlock(Ids::REDSTONE_LAMP, fn($bid, $name, $info) => new BlockRedstoneLamp($bid, $name, $info));
        foreach ([
             Ids::OAK_DOOR_BLOCK, Ids::SPRUCE_DOOR_BLOCK, Ids::BIRCH_DOOR_BLOCK,
             Ids::JUNGLE_DOOR_BLOCK, Ids::ACACIA_DOOR_BLOCK, Ids::DARK_OAK_DOOR_BLOCK
        ] as $id) {
            $this->registerBlock($id, fn($bid, $name, $info) => new BlockWoodenDoor($bid, $name, $info));
        }

        // power
        $this->registerBlock(Ids::LEVER, fn($bid, $name, $info) => new BlockLever($bid, $name, $info));
        $this->registerBlock(Ids::REDSTONE_BLOCK, fn($bid, $name, $info) => new BlockRedstone($bid, $name, $info));
        $this->registerBlock(Ids::REDSTONE_TORCH, fn($bid, $name, $info) => new BlockRedstoneTorch($bid, $name, $info));
        $this->registerBlock(Ids::STONE_BUTTON, fn($bid, $name, $info) => new BlockStoneButton($bid, $name, $info));
        $this->registerBlock(Ids::STONE_PRESSURE_PLATE, fn($bid, $name, $info) => new BlockStonePressurePlate($bid, $name, $info));
        $this->registerBlock(Ids::HEAVY_WEIGHTED_PRESSURE_PLATE, fn($bid, $name, $info) => new BlockWeightedPressurePlateHeavy($bid, $name, $info));
        $this->registerBlock(Ids::LIGHT_WEIGHTED_PRESSURE_PLATE, fn($bid, $name, $info) => new BlockWeightedPressurePlateLight($bid, $name, $info));
        foreach ([
            Ids::WOODEN_BUTTON, Ids::ACACIA_BUTTON, Ids::BIRCH_BUTTON,
            Ids::DARK_OAK_BUTTON, Ids::JUNGLE_BUTTON, Ids::SPRUCE_BUTTON
        ] as $id) {
            $this->registerBlock($id, fn($bid, $name, $info) => new BlockWoodenButton($bid, $name, $info));
        }
        $this->registerBlock(Ids::WOODEN_PRESSURE_PLATE, fn($bid, $name, $info) => new BlockWoodenPressurePlate($bid, $name, $info));

        // transmission
        $this->registerBlock(Ids::UNPOWERED_COMPARATOR, fn($bid, $name, $info) => new BlockRedstoneComparator($bid, $name, $info));
        $this->registerBlock(Ids::UNPOWERED_REPEATER, fn($bid, $name, $info) => new BlockRedstoneRepeater($bid, $name, $info));
        $this->registerBlock(Ids::REDSTONE_WIRE, fn($bid, $name, $info) => new BlockRedstoneWire($bid, $name, $info));

        ItemFactory::getInstance()->register(new ItemRedstone(new ItemIdentifier(ItemIds::REDSTONE, 0), "Redstone"), true);
    }

    private function registerBlock(int $id, $callback): void {
        $factory = BlockFactory::getInstance();
        $oldBlock = $factory->get($id, 0);
        $block = $callback($oldBlock->getIdInfo(), $oldBlock->getName(), $oldBlock->getBreakInfo());
        $factory->register($block, true);
    }
}