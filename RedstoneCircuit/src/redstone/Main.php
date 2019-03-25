<?php

namespace redstone;

use pocketmine\plugin\PluginBase;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIdentifier as BID;

use pocketmine\item\{Item, ItemIds};
use pocketmine\item\ItemBlock;
use pocketmine\item\ItemFactory;

use pocketmine\tile\{Tile, TileFactory};


use redstone\blockEntities\BlockEntityChest;
use redstone\blockEntities\BlockEntityCommandBlock;
use redstone\blockEntities\BlockEntityDaylightDetector;
use redstone\blockEntities\BlockEntityDispenser;
use redstone\blockEntities\BlockEntityDropper;
use redstone\blockEntities\BlockEntityHopper;
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
use redstone\blocks\BlockNote;
use redstone\blocks\BlockObserver;
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
use redstone\blocks\BlockTrappedChest;
use redstone\blocks\BlockTripwire;
use redstone\blocks\BlockTripwireHook;
use redstone\blocks\BlockWeightedPressurePlateLight;
use redstone\blocks\BlockWeightedPressurePlateHeavy;
use redstone\blocks\BlockWoodenDoor;

use redstone\listeners\EventListener;
use redstone\listeners\ScheduledBlockUpdateListener;

use redstone\utils\CustomConfig;
use redstone\utils\ScheduledBlockUpdateLoader;

class Main extends PluginBase {

    private static $instance;

    public static function getInstance() : Main {
        return Main::$instance;
    }

    private $config;

    private $scheduledBlockUpdateLoader;

    public function onEnable() {
        Main::$instance = $this;

        $this->config = new CustomConfig();

        $this->scheduledBlockUpdateLoader = new ScheduledBlockUpdateLoader();

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

        $this->initBlocks();
        $this->initBlockEntities();
        $this->initItems();
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

    private function initBlocks() : void {
		BlockFactory::register(new BlockRedstoneWire(new BID(Block::REDSTONE_WIRE, ItemIds::REDSTONE), "Redstone Wire"), true);

		BlockFactory::register(new BlockRedstoneTorch(new BID(Block::REDSTONE_TORCH), "Redstone Torch"), true);
		BlockFactory::register(new BlockRedstoneTorchUnlit(new BID(Block::UNLIT_REDSTONE_TORCH), "Unlit Redstone Torch"), true);

		BlockFactory::register(new BlockRedstoneRepeaterPowered(new BID(Block::POWERED_REPEATER), "Powered Repeater"), true);
		BlockFactory::register(new BlockRedstoneRepeaterUnpowered(new BID(Block::UNPOWERED_REPEATER, ItemIds::REPEATER), "Unpowered Repeater"), true);

		BlockFactory::register(new BlockRedstoneComparatorPowered(new BID(Block::POWERED_COMPARATOR), "Powered Comparator"), true);
		BlockFactory::register(new BlockRedstoneComparatorUnpowered(new BID(Block::UNPOWERED_COMPARATOR, ItemIds::COMPARATOR), "Unpowered Comparator"), true);


		BlockFactory::register(new BlockRedstone(new BID(Block::REDSTONE_BLOCK), "Redstone Block"), true);

		BlockFactory::register(new BlockLever(new BID(Block::LEVER), "Lever"), true);

		BlockFactory::register(new BlockButtonStone(new BID(Block::STONE_BUTTON), "Stone Button"), true);
		BlockFactory::register(new BlockButtonWooden(new BID(Block::WOODEN_BUTTON), "Wooden Button"), true);

		BlockFactory::register(new BlockPressurePlateStone(new BID(Block::STONE_PRESSURE_PLATE), "Stone Pressure Plate"), true);
		BlockFactory::register(new BlockPressurePlateWooden(new BID(Block::WOODEN_PRESSURE_PLATE), "Wooden Pressure Plate"), true);
		BlockFactory::register(new BlockWeightedPressurePlateHeavy(new BID(Block::HEAVY_WEIGHTED_PRESSURE_PLATE), "Heavy Weighted Pressure Plate"), true);
		BlockFactory::register(new BlockWeightedPressurePlateLight(new BID(Block::LIGHT_WEIGHTED_PRESSURE_PLATE), "Light Weighted Pressure Plate"), true);

		BlockFactory::register(new BlockDayLightDetector(new BID(Block::DAYLIGHT_SENSOR), "Daylight Sensor"), true);
        BlockFactory::register(new BlockDayLightDetectorInverted(ne BID(Block::DAYLIGHT_DETECTOR_INVERTED), "DayLightDetectorInverted"), true);
        BlockFactory::register(new BlockObserver(new BID(Block::OBSERVER), "Observer"), true);
        BlockFactory::register(new BlockTrappedChest(new BID(Block::TRAPPED_CHEST), "Trapped Chest"), true);
        
        BlockFactory::registerBlock(new BlockTripwireHook(new BID(Block::TRIPWIRE_HOOK), "Tripwire Hook"), true);
        BlockFactory::registerBlock(new BlockTripwire(new BID(Block::TRIPWIRE), "Tripwire"), true);

		BlockFactory::register(new BlockRedstoneLamp(new BID(Block::REDSTONE_LAMP), "Redstone Lamp"), true);
		BlockFactory::register(new BlockRedstoneLampLit(new BID(Block::LIT_REDSTONE_LAMP), "Lit Redstone Lamp"), true);

		BlockFactory::register(new BlockNote(new BID(Block::NOTEBLOCK), "Note Block"), true);

		BlockFactory::register(new BlockDropper(new BID(Block::DROPPER), "Dropper"), true);
		BlockFactory::register(new BlockDispenser(new BID(Block::DISPENSER), "Dispenser"), true);
		
		//BlockFactory::registerBlock(new BlockPiston(), true);

		BlockFactory::register(new BlockCommand(new BID(Block::COMMAND_BLOCK), "Command Block"), true);
		BlockFactory::register(new BlockCommandRepeating(new BID(Block::REPEATING_COMMAND_BLOCK), "Command Block Repeating"), true);
		BlockFactory::register(new BlockCommandChain(new BID(Block::CHAIN_COMMAND_BLOCK), "Command Block Chain"), true);

		BlockFactory::register(new BlockTNT(new BID(Block::TNT), "TNT"), true);

		BlockFactory::register(new BlockWoodenDoor(new BID(Block::OAK_DOOR_BLOCK, 0, ItemIds::OAK_DOOR), "Oak Door"), true);
		BlockFactory::register(new BlockWoodenDoor(new BID(Block::SPRUCE_DOOR_BLOCK, 0, ItemIds::SPRUCE_DOOR), "Spruce Door"), true);
		BlockFactory::register(new BlockWoodenDoor(new BID(Block::BIRCH_DOOR_BLOCK, 0, ItemIds::BIRCH_DOOR), "Wooden Door"), true);
		BlockFactory::register(new BlockWoodenDoor(new BID(Block::JUNGLE_DOOR_BLOCK, 0, ItemIds::JUNGLE_DOOR), "Jungle Door"), true);
		BlockFactory::register(new BlockWoodenDoor(new BID(Block::ACACIA_DOOR_BLOCK, 0, ItemIds::ACACIA_DOOR), "Acacia Door"), true);
		BlockFactory::register(new BlockWoodenDoor(new BID(Block::DARK_OAK_DOOR_BLOCK, 0, ItemIds::DARK_OAK_DOOR), "Dark Oak Door"), true);

		BlockFactory::register(new BlockIronDoor(new BID(Block::IRON_DOOR_BLOCK, 0, ItemIds::IRON_DOOR), "Iron Door"), true);

		BlockFactory::register(new BlockTrapdoor(new BID(Block::TRAPDOOR), "Trapdoor"), true);
		BlockFactory::register(new BlockIronTrapdoor(new BID(Block::IRON_TRAPDOOR), "Iron Trapdoor"), true);
		
		BlockFactory::register(new BlockFenceGate(new BID(Block::OAK_FENCE_GATE, 0), "Oak Fence Gate"), true);
		BlockFactory::register(new BlockFenceGate(new BID(Block::SPRUCE_FENCE_GATE, 0), "Spruce Fence Gate"), true);
		BlockFactory::register(new BlockFenceGate(new BID(Block::BIRCH_FENCE_GATE, 0), "Birch Fence Gate"), true);
		BlockFactory::register(new BlockFenceGate(new BID(Block::JUNGLE_FENCE_GATE, 0), "Jungle Fence Gate"), true);
		BlockFactory::register(new BlockFenceGate(new BID(Block::DARK_OAK_FENCE_GATE, 0), "Dark Oak Fence Gate"), true);
		BlockFactory::register(new BlockFenceGate(new BID(Block::ACACIA_FENCE_GATE, 0), "Acacia Fence Gate"), true);
    }

    private function initBlockEntities() : void {
        TileFactory::register(BlockEntityChest::class, ["Chest", "minecraft:chest"]);
        TileFactory::register(BlockEntityCommandBlock::class, ["CommandBlock", "minecraft:command_block"]);
        TileFactory::register(BlockEntityDaylightDetector::class, ["DaylightDetector", "minecraft:daylight_detector"]);
        TileFactory::register(BlockEntityDropper::class, ["Dropper", "minecraft:dropper"]);
        TileFactory::register(BlockEntityDispenser::class, ["Dispenser", "minecraft:dispenser"]);
        TileFactory::register(BlockEntityHopper::class, ["Hopper", "minecraft:hopper"]);
        TileFactory::register(BlockEntityNoteBlock::class, ["NoteBlock", "minecraft:note_block"]);
        TileFactory::register(BlockEntityObserver::class, ["Observer", "minecraft:observer"]);
        //TileFactory::register(BlockEntityPistonArm::class, ["PistonArm", "minecraft:piston_arm"]);
        TileFactory::register(BlockEntityRedstoneComparator::class, ["Comparator", "minecraft:comparator"]);
    }

    private function initItems() : void {
		ItemFactory::register(new ItemBlock(Block::UNPOWERED_REPEATER, 0, Item::REPEATER), true);
		ItemFactory::register(new ItemBlock(Block::UNPOWERED_COMPARATOR, 0, Item::COMPARATOR), true);
		ItemFactory::register(new ItemBlock(Block::COMMAND_BLOCK, 0, Item::COMMAND_BLOCK), true);
		ItemFactory::register(new ItemBlock(Block::DROPPER, 0, Item::DROPPER), true);
		ItemFactory::register(new ItemBlock(Block::DISPENSER, 0, Item::DISPENSER), true);
        ItemFactory::register(new ItemBlock(Block::OBSERVER, 0, Item::OBSERVER), true);
        //ItemFactory::registerItem(new ItemBlock(Block::PISTON, 0, Item::PISTON), true);
    }

    private function initCreativeItem() : void {
        Item::addCreativeItem(Item::get(Item::OBSERVER));
        Item::addCreativeItem(Item::get(Item::REPEATER));
        Item::addCreativeItem(Item::get(Item::COMPARATOR));
        Item::addCreativeItem(Item::get(Item::HOPPER));
        Item::addCreativeItem(Item::get(Item::COMMAND_BLOCK));
        Item::addCreativeItem(Item::get(Item::DROPPER));
        Item::addCreativeItem(Item::get(Item::DISPENSER));
        //Item::addCreativeItem(Item::get(Item::PISTON));
    }
}
