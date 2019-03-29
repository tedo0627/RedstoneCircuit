<?php

namespace redstone;

use pocketmine\plugin\PluginBase;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;

use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\item\ItemFactory;

use pocketmine\tile\Tile;


use redstone\blockEntities\BlockEntityChest;
use redstone\blockEntities\BlockEntityCommandBlock;
use redstone\blockEntities\BlockEntityDaylightDetector;
use redstone\blockEntities\BlockEntityDispenser;
use redstone\blockEntities\BlockEntityDropper;
use redstone\blockEntities\BlockEntityHopper;
use redstone\blockEntities\BlockEntityMovingBlock;
use redstone\blockEntities\BlockEntityNoteBlock;
use redstone\blockEntities\BlockEntityObserver;
use redstone\blockEntities\BlockEntityPistonArm;
use redstone\blockEntities\BlockEntityRedstoneComparator;

use redstone\blocks\BlockButtonStone;
use redstone\blocks\BlockButtonWooden;
use redstone\blocks\BlockCommand;
use redstone\blocks\BlockCommandChain;
use redstone\blocks\BlockCommandRepeating;
use redstone\blocks\BlockDaylightDetector;
use redstone\blocks\BlockDaylightDetectorInverted;
use redstone\blocks\BlockDispenser;
use redstone\blocks\BlockDropper;
use redstone\blocks\BlockFenceGate;
use redstone\blocks\BlockHopper;
use redstone\blocks\BlockIronDoor;
use redstone\blocks\BlockIronTrapdoor;
use redstone\blocks\BlockLever;
use redstone\blocks\BlockMoving;
use redstone\blocks\BlockNote;
use redstone\blocks\BlockObserver;
use redstone\blocks\BlockPiston;
use redstone\blocks\BlockPistonarmcollision;
use redstone\blocks\BlockPistonSticky;
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
use redstone\blocks\BlockSlime;
use redstone\blocks\BlockTNT;
use redstone\blocks\BlockTrapdoor;
use redstone\blocks\BlockTrappedChest;
use redstone\blocks\BlockTripwire;
use redstone\blocks\BlockTripwireHook;
use redstone\blocks\BlockWeightedPressurePlateLight;
use redstone\blocks\BlockWeightedPressurePlateHeavy;
use redstone\blocks\BlockWoodenDoor;

use redstone\listeners\EventListener;
use redstone\listeners\ScheduledBlockUpdateListener;

use redstone\utils\CustomConfig;
use redstone\utils\GlobalBlockPalette;
use redstone\utils\ScheduledBlockUpdateLoader;

class Main extends PluginBase {

    private static $instance;

    public static function getInstance() : Main {
        return Main::$instance;
    }

    private $config;

    private $scheduledBlockUpdateLoader;

    private $palette;

    public function onEnable() {
        Main::$instance = $this;

        $this->config = new CustomConfig();

        $this->scheduledBlockUpdateLoader = new ScheduledBlockUpdateLoader();

        $this->palette = new GlobalBlockPalette();

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

        $this->initBlocks();
        $this->initBlockEntities();
        $this->initCreativeItem();
    }

    public function onDisable() {
        if (!$this->scheduledBlockUpdateLoader->isActivate()) {
            return;
        }

        foreach($this->getServer()->getLevels() as $level){
            $this->scheduledBlockUpdateLoader->saveLevel($level);
        }
    }

    public function getCustomConfig() : CustomConfig {
        return $this->config;
    }

    public function getScheduledBlockUpdateLoader() : ScheduledBlockUpdateLoader {
        return $this->scheduledBlockUpdateLoader;
    }

    public function getGlobalBlockPalette() : GlobalBlockPalette {
        return $this->palette;
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

        BlockFactory::registerBlock(new BlockDaylightDetector(), true);
        BlockFactory::registerBlock(new BlockDaylightDetectorInverted(), true);

        BlockFactory::registerBlock(new BlockObserver(), true);

        BlockFactory::registerBlock(new BlockTrappedChest(), true);
        
        BlockFactory::registerBlock(new BlockTripwireHook(), true);
        BlockFactory::registerBlock(new BlockTripwire(), true);


        BlockFactory::registerBlock(new BlockRedstoneLamp(), true);
        BlockFactory::registerBlock(new BlockRedstoneLampLit(), true);

        BlockFactory::registerBlock(new BlockNote(), true);

        BlockFactory::registerBlock(new BlockDropper(), true);
        BlockFactory::registerBlock(new BlockDispenser(), true);

        BlockFactory::registerBlock(new BlockHopper(), true);

        BlockFactory::registerBlock(new BlockPiston(), true);
        BlockFactory::registerBlock(new BlockPistonarmcollision(), true);
        BlockFactory::registerBlock(new BlockPistonSticky(), true);
        BlockFactory::registerBlock(new BlockMoving(), true);

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

        BlockFactory::registerBlock(new BlockSlime(), true);
    }

    private function initBlockEntities() : void {
        Tile::registerTile(BlockEntityChest::class, ["Chest", "minecraft:chest"]);
        Tile::registerTile(BlockEntityCommandBlock::class, ["CommandBlock", "minecraft:command_block"]);
        Tile::registerTile(BlockEntityDaylightDetector::class, ["DaylightDetector", "minecraft:daylight_detector"]);
        Tile::registerTile(BlockEntityDropper::class, ["Dropper", "minecraft:dropper"]);
        Tile::registerTile(BlockEntityDispenser::class, ["Dispenser", "minecraft:dispenser"]);
        Tile::registerTile(BlockEntityHopper::class, ["Hopper", "minecraft:hopper"]);
        Tile::registerTile(BlockEntityMovingBlock::class, ["Movingblock", "minecraft:movingblock"]);
        Tile::registerTile(BlockEntityNoteBlock::class, ["NoteBlock", "minecraft:note_block"]);
        Tile::registerTile(BlockEntityObserver::class, ["Observer", "minecraft:observer"]);
        Tile::registerTile(BlockEntityPistonArm::class, ["PistonArm", "minecraft:piston_arm"]);
        Tile::registerTile(BlockEntityRedstoneComparator::class, ["Comparator", "minecraft:comparator"]);
    }

    private function initCreativeItem() : void {
        Item::initCreativeItems();
        Item::addCreativeItem(Item::get(Item::PISTON));
        Item::addCreativeItem(Item::get(Item::STICKY_PISTON));
    }
}
