<?php

namespace redstone;

use pocketmine\plugin\PluginBase;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;

use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\item\ItemFactory;

use pocketmine\tile\Tile;


use redstone\blockEntities\BlockEntityCommandBlock;
use redstone\blockEntities\BlockEntityDispenser;
use redstone\blockEntities\BlockEntityDropper;
use redstone\blockEntities\BlockEntityHopper;
use redstone\blockEntities\BlockEntityNoteBlock;
use redstone\blockEntities\BlockEntityPistonArm;
use redstone\blockEntities\BlockEntityRedstoneComparator;

use redstone\blocks\BlockButtonStone;
use redstone\blocks\BlockButtonWooden;
use redstone\blocks\BlockCommand;
use redstone\blocks\BlockCommandChain;
use redstone\blocks\BlockCommandRepeating;
use redstone\blocks\BlockDispenser;
use redstone\blocks\BlockDropper;
use redstone\blocks\BlockFenceGate;
use redstone\blocks\BlockHopper;
use redstone\blocks\BlockIronDoor;
use redstone\blocks\BlockIronTrapdoor;
use redstone\blocks\BlockLever;
use redstone\blocks\BlockNote;
use redstone\blocks\BlockPiston;
use redstone\blocks\BlockPressurePlateStone;
use redstone\blocks\BlockPressurePlateWooden;
use redstone\blocks\BlockRedstone;
use redstone\blocks\BlockRedstoneComparatorPowered;
use redstone\blocks\BlockRedstoneComparatorUnpowered;
use redstone\blocks\BlockRedstoneLamp;
use redstone\blocks\BlockRedstoneLampLit;
use redstone\blocks\BlockRedstoneRepeaterPowered;
use redstone\blocks\BlockRedstoneRepeaterUnpowered;
use redstone\blocks\BlockRedstoneTorch;
use redstone\blocks\BlockRedstoneTorchUnlit;
use redstone\blocks\BlockRedstoneWire;
use redstone\blocks\BlockTNT;
use redstone\blocks\BlockTrapdoor;
use redstone\blocks\BlockWeightedPressurePlateLight;
use redstone\blocks\BlockWeightedPressurePlateHeavy;
use redstone\blocks\BlockWoodenDoor;

class Main extends PluginBase {

    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

        $this->initBlocks();
        $this->initBlockEntities();
        $this->initItems();
        $this->initCreativeItem();
    }

    private function initBlocks() : void {
        BlockFactory::registerBlock(new BlockRedstoneWire(), true);

        BlockFactory::registerBlock(new BlockRedstoneTorch(), true);
        BlockFactory::registerBlock(new BlockRedstoneTorchUnlit(), true);

        BlockFactory::registerBlock(new BlockRedstoneRepeaterPowered(), true);
        BlockFactory::registerBlock(new BlockRedstoneRepeaterUnpowered(), true);

        BlockFactory::registerBlock(new BlockRedstoneComparatorPowered(), true);
        BlockFactory::registerBlock(new BlockRedstoneComparatorUnpowered(), true);


        BlockFactory::registerBlock(new BlockRedstone(), true);

        BlockFactory::registerBlock(new BlockLever(), true);

        BlockFactory::registerBlock(new BlockButtonStone(), true);
        BlockFactory::registerBlock(new BlockButtonWooden(), true);

        BlockFactory::registerBlock(new BlockPressurePlateStone(), true);
        BlockFactory::registerBlock(new BlockPressurePlateWooden(), true);
        BlockFactory::registerBlock(new BlockWeightedPressurePlateLight(), true);
        BlockFactory::registerBlock(new BlockWeightedPressurePlateHeavy(), true);


        BlockFactory::registerBlock(new BlockRedstoneLamp(), true);
        BlockFactory::registerBlock(new BlockRedstoneLampLit(), true);

        BlockFactory::registerBlock(new BlockNote(), true);

        BlockFactory::registerBlock(new BlockDropper(), true);
        BlockFactory::registerBlock(new BlockDispenser(), true);

        BlockFactory::registerBlock(new BlockHopper(), true);

        //BlockFactory::registerBlock(new BlockPiston(), true);

        BlockFactory::registerBlock(new BlockCommand(), true);
        BlockFactory::registerBlock(new BlockCommandRepeating(), true);
        BlockFactory::registerBlock(new BlockCommandChain(), true);

        BlockFactory::registerBlock(new BlockTNT(), true);

        BlockFactory::registerBlock(new BlockWoodenDoor(Block::OAK_DOOR_BLOCK, 0, "Oak Door", Item::OAK_DOOR), true);
        BlockFactory::registerBlock(new BlockWoodenDoor(Block::SPRUCE_DOOR_BLOCK, 0, "Spruce Door", Item::SPRUCE_DOOR), true);
        BlockFactory::registerBlock(new BlockWoodenDoor(Block::BIRCH_DOOR_BLOCK, 0, "Birch Door", Item::BIRCH_DOOR), true);
        BlockFactory::registerBlock(new BlockWoodenDoor(Block::JUNGLE_DOOR_BLOCK, 0, "Jungle Door", Item::JUNGLE_DOOR), true);
        BlockFactory::registerBlock(new BlockWoodenDoor(Block::ACACIA_DOOR_BLOCK, 0, "Acacia Door", Item::ACACIA_DOOR), true);
        BlockFactory::registerBlock(new BlockWoodenDoor(Block::DARK_OAK_DOOR_BLOCK, 0, "Dark Oak Door", Item::DARK_OAK_DOOR), true);

        BlockFactory::registerBlock(new BlockIronDoor(), true);

        BlockFactory::registerBlock(new BlockTrapdoor(), true);
        BlockFactory::registerBlock(new BlockIronTrapdoor(), true);
        
        BlockFactory::registerBlock(new BlockFenceGate(Block::OAK_FENCE_GATE, 0, "Oak Fence Gate"), true);
        BlockFactory::registerBlock(new BlockFenceGate(Block::SPRUCE_FENCE_GATE, 0, "Spruce Fence Gate"), true);
        BlockFactory::registerBlock(new BlockFenceGate(Block::BIRCH_FENCE_GATE, 0, "Birch Fence Gate"), true);
        BlockFactory::registerBlock(new BlockFenceGate(Block::JUNGLE_FENCE_GATE, 0, "Jungle Fence Gate"), true);
        BlockFactory::registerBlock(new BlockFenceGate(Block::DARK_OAK_FENCE_GATE, 0, "Dark Oak Fence Gate"), true);
        BlockFactory::registerBlock(new BlockFenceGate(Block::ACACIA_FENCE_GATE, 0, "Acacia Fence Gate"), true);
    }

    private function initBlockEntities() : void {
        Tile::registerTile(BlockEntityCommandBlock::class, ["CommandBlock", "minecraft:command_block"]);
        Tile::registerTile(BlockEntityDropper::class, ["Dropper", "minecraft:dropper"]);
        Tile::registerTile(BlockEntityDispenser::class, ["Dispenser", "minecraft:dispenser"]);
        Tile::registerTile(BlockEntityHopper::class, ["Hopper", "minecraft:hopper"]);
        Tile::registerTile(BlockEntityNoteBlock::class, ["NoteBlock", "minecraft:note_block"]);
        Tile::registerTile(BlockEntityRedstoneComparator::class, ["Comparator", "minecraft:comparator"]);
        //Tile::registerTile(BlockEntityPistonArm::class, ["PistonArm", "minecraft:piston_arm"]);
    }

    private function initItems() : void {
        ItemFactory::registerItem(new ItemBlock(Block::UNPOWERED_REPEATER, 0, Item::REPEATER), true);
        ItemFactory::registerItem(new ItemBlock(Block::UNPOWERED_COMPARATOR, 0, Item::COMPARATOR), true);
        ItemFactory::registerItem(new ItemBlock(Block::COMMAND_BLOCK, 0, Item::COMMAND_BLOCK), true);
        ItemFactory::registerItem(new ItemBlock(Block::DROPPER, 0, Item::DROPPER), true);
        ItemFactory::registerItem(new ItemBlock(Block::DISPENSER, 0, Item::DISPENSER), true);
        ItemFactory::registerItem(new ItemBlock(Block::PISTON, 0, Item::PISTON), true);
    }

    private function initCreativeItem() : void {
        Item::addCreativeItem(Item::get(Item::REPEATER));
        Item::addCreativeItem(Item::get(Item::COMPARATOR));
        Item::addCreativeItem(Item::get(Item::HOPPER));
        Item::addCreativeItem(Item::get(Item::COMMAND_BLOCK));
        Item::addCreativeItem(Item::get(Item::DROPPER));
        Item::addCreativeItem(Item::get(Item::DISPENSER));
        //Item::addCreativeItem(Item::get(Item::PISTON));
    }
}