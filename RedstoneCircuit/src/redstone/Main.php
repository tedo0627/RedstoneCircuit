<?php

namespace redstone;

use pocketmine\plugin\PluginBase;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;

use pocketmine\item\Item;

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
        if ($this->getCustomConfig()->isEnableRedstoneWire()) {
            BlockFactory::registerBlock(new BlockRedstoneWire(), true);
        }

        if ($this->getCustomConfig()->isEnableRedstoneRepeater()) {
            BlockFactory::registerBlock(new BlockRedstoneRepeaterPowered(), true);
            BlockFactory::registerBlock(new BlockRedstoneRepeaterUnpowered(), true);
        }

        if ($this->getCustomConfig()->isEnableRedstoneComparator()) {
            BlockFactory::registerBlock(new BlockRedstoneComparatorPowered(), true);
            BlockFactory::registerBlock(new BlockRedstoneComparatorUnpowered(), true);
        }

        if ($this->getCustomConfig()->isEnableRedstoneTorch()) {
            BlockFactory::registerBlock(new BlockRedstoneTorch(), true);
            BlockFactory::registerBlock(new BlockRedstoneTorchUnlit(), true);
        }


        if ($this->getCustomConfig()->isEnableRedstoneBlock()) {
            BlockFactory::registerBlock(new BlockRedstone(), true);
        }

        if ($this->getCustomConfig()->isEnableLever()) {
            BlockFactory::registerBlock(new BlockLever(), true);
        }

        if ($this->getCustomConfig()->isEnableButton()) {
            BlockFactory::registerBlock(new BlockButtonStone(), true);
            BlockFactory::registerBlock(new BlockButtonWooden(), true);
        }

        if ($this->getCustomConfig()->isEnablePressurePlate()) {
            BlockFactory::registerBlock(new BlockPressurePlateStone(), true);
            BlockFactory::registerBlock(new BlockPressurePlateWooden(), true);
            BlockFactory::registerBlock(new BlockWeightedPressurePlateLight(), true);
            BlockFactory::registerBlock(new BlockWeightedPressurePlateHeavy(), true);
        }

        if ($this->getCustomConfig()->isEnableDaylightDetector()) {
            BlockFactory::registerBlock(new BlockDaylightDetector(), true);
            BlockFactory::registerBlock(new BlockDaylightDetectorInverted(), true);
        }

        if ($this->getCustomConfig()->isEnableObserver()) {
            BlockFactory::registerBlock(new BlockObserver(), true);
        }

        if ($this->getCustomConfig()->isEnableTrappedChest()) {
            BlockFactory::registerBlock(new BlockTrappedChest(), true);
        }

        if ($this->getCustomConfig()->isEnableTripwire()) {
            BlockFactory::registerBlock(new BlockTripwireHook(), true);
            BlockFactory::registerBlock(new BlockTripwire(), true);
        }

        if ($this->getCustomConfig()->isEnableRedstoneLamp()) {
            BlockFactory::registerBlock(new BlockRedstoneLamp(), true);
            BlockFactory::registerBlock(new BlockRedstoneLampLit(), true);
        }

        if ($this->getCustomConfig()->isEnableNoteBlock()) {
            BlockFactory::registerBlock(new BlockNote(), true);
        }

        if ($this->getCustomConfig()->isEnableDropper()) {
            BlockFactory::registerBlock(new BlockDropper(), true);
        }

        if ($this->getCustomConfig()->isEnableDispenser()) {
            BlockFactory::registerBlock(new BlockDispenser(), true);
        }

        if ($this->getCustomConfig()->isEnableHopper()) {
            BlockFactory::registerBlock(new BlockHopper(), true);
        }

        if ($this->getCustomConfig()->isEnablePiston()) {
            BlockFactory::registerBlock(new BlockPiston(), true);
            BlockFactory::registerBlock(new BlockPistonarmcollision(), true);
            BlockFactory::registerBlock(new BlockPistonSticky(), true);
            BlockFactory::registerBlock(new BlockMoving(), true);
        }

        if ($this->getCustomConfig()->isEnableCommandBlock()) {
            BlockFactory::registerBlock(new BlockCommand(), true);
            BlockFactory::registerBlock(new BlockCommandRepeating(), true);
            BlockFactory::registerBlock(new BlockCommandChain(), true);
        }

        if ($this->getCustomConfig()->isEnableTnt()) {
            BlockFactory::registerBlock(new BlockTNT(), true);
        }

        if ($this->getCustomConfig()->isEnableDoor()) {
            BlockFactory::registerBlock(new BlockWoodenDoor(Block::OAK_DOOR_BLOCK, 0, "Oak Door", Item::OAK_DOOR), true);
            BlockFactory::registerBlock(new BlockWoodenDoor(Block::SPRUCE_DOOR_BLOCK, 0, "Spruce Door", Item::SPRUCE_DOOR), true);
            BlockFactory::registerBlock(new BlockWoodenDoor(Block::BIRCH_DOOR_BLOCK, 0, "Birch Door", Item::BIRCH_DOOR), true);
            BlockFactory::registerBlock(new BlockWoodenDoor(Block::JUNGLE_DOOR_BLOCK, 0, "Jungle Door", Item::JUNGLE_DOOR), true);
            BlockFactory::registerBlock(new BlockWoodenDoor(Block::ACACIA_DOOR_BLOCK, 0, "Acacia Door", Item::ACACIA_DOOR), true);
            BlockFactory::registerBlock(new BlockWoodenDoor(Block::DARK_OAK_DOOR_BLOCK, 0, "Dark Oak Door", Item::DARK_OAK_DOOR), true);
            BlockFactory::registerBlock(new BlockIronDoor(), true);
        }

        if ($this->getCustomConfig()->isEnableTrapDoor()) {
            BlockFactory::registerBlock(new BlockTrapdoor(), true);
            BlockFactory::registerBlock(new BlockIronTrapdoor(), true);
        }

        if ($this->getCustomConfig()->isEnableFenceGate()) {
            BlockFactory::registerBlock(new BlockFenceGate(Block::OAK_FENCE_GATE, 0, "Oak Fence Gate"), true);
            BlockFactory::registerBlock(new BlockFenceGate(Block::SPRUCE_FENCE_GATE, 0, "Spruce Fence Gate"), true);
            BlockFactory::registerBlock(new BlockFenceGate(Block::BIRCH_FENCE_GATE, 0, "Birch Fence Gate"), true);
            BlockFactory::registerBlock(new BlockFenceGate(Block::JUNGLE_FENCE_GATE, 0, "Jungle Fence Gate"), true);
            BlockFactory::registerBlock(new BlockFenceGate(Block::DARK_OAK_FENCE_GATE, 0, "Dark Oak Fence Gate"), true);
            BlockFactory::registerBlock(new BlockFenceGate(Block::ACACIA_FENCE_GATE, 0, "Acacia Fence Gate"), true);
        }


        if ($this->getCustomConfig()->isEnableSlimeBlock()) {
            BlockFactory::registerBlock(new BlockSlime(), true);
        }
    }

    private function initBlockEntities() : void {
        if ($this->getCustomConfig()->isEnableTrappedChest()) {
            Tile::registerTile(BlockEntityChest::class, ["Chest", "minecraft:chest"]);
        }
        if ($this->getCustomConfig()->isEnableCommandBlock()) {
            Tile::registerTile(BlockEntityCommandBlock::class, ["CommandBlock", "minecraft:command_block"]);
        }
        if ($this->getCustomConfig()->isEnableDaylightDetector()) {
            Tile::registerTile(BlockEntityDaylightDetector::class, ["DaylightDetector", "minecraft:daylight_detector"]);
        }
        if ($this->getCustomConfig()->isEnableDropper()) {
            Tile::registerTile(BlockEntityDropper::class, ["Dropper", "minecraft:dropper"]);
        }
        if ($this->getCustomConfig()->isEnableDispenser()) {
            Tile::registerTile(BlockEntityDispenser::class, ["Dispenser", "minecraft:dispenser"]);
        }
        if ($this->getCustomConfig()->isEnableHopper()) {
            Tile::registerTile(BlockEntityHopper::class, ["Hopper", "minecraft:hopper"]);
        }
        if ($this->getCustomConfig()->isEnableNoteBlock()) {
            Tile::registerTile(BlockEntityNoteBlock::class, ["NoteBlock", "minecraft:note_block"]);
        }
        if ($this->getCustomConfig()->isEnableObserver()) {
            Tile::registerTile(BlockEntityObserver::class, ["Observer", "minecraft:observer"]);
        }
        if ($this->getCustomConfig()->isEnablePiston()) {
            Tile::registerTile(BlockEntityMovingBlock::class, ["Movingblock", "minecraft:movingblock"]);
            Tile::registerTile(BlockEntityPistonArm::class, ["PistonArm", "minecraft:piston_arm"]);
        }
        if ($this->getCustomConfig()->isEnableRedstoneComparator()) {
            Tile::registerTile(BlockEntityRedstoneComparator::class, ["Comparator", "minecraft:comparator"]);
        }
    }

    private function initCreativeItem() : void {
        Item::initCreativeItems();
    }
}
