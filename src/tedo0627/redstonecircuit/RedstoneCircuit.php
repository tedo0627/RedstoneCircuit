<?php

namespace tedo0627\redstonecircuit;

use Closure;
use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockIdentifierFlattened;
use pocketmine\block\BlockLegacyIds as Ids;
use pocketmine\block\BlockToolType;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\ToolTier;
use pocketmine\plugin\PluginBase;
use tedo0627\redstonecircuit\block\entity\BlockEntityChest;
use tedo0627\redstonecircuit\block\entity\BlockEntityCommand;
use tedo0627\redstonecircuit\block\entity\BlockEntityDispenser;
use tedo0627\redstonecircuit\block\entity\BlockEntityDropper;
use tedo0627\redstonecircuit\block\entity\BlockEntityHopper;
use tedo0627\redstonecircuit\block\entity\BlockEntityNote;
use tedo0627\redstonecircuit\block\entity\BlockEntityObserver;
use tedo0627\redstonecircuit\block\entity\BlockEntitySkull;
use tedo0627\redstonecircuit\block\mechanism\BlockActivatorRail;
use tedo0627\redstonecircuit\block\mechanism\BlockCommand;
use tedo0627\redstonecircuit\block\mechanism\BlockDispenser;
use tedo0627\redstonecircuit\block\mechanism\BlockDropper;
use tedo0627\redstonecircuit\block\mechanism\BlockFenceGate;
use tedo0627\redstonecircuit\block\mechanism\BlockHopper;
use tedo0627\redstonecircuit\block\mechanism\BlockIronDoor;
use tedo0627\redstonecircuit\block\mechanism\BlockIronTrapdoor;
use tedo0627\redstonecircuit\block\mechanism\BlockNote;
use tedo0627\redstonecircuit\block\mechanism\BlockPoweredRail;
use tedo0627\redstonecircuit\block\mechanism\BlockRedstoneLamp;
use tedo0627\redstonecircuit\block\mechanism\BlockSkull;
use tedo0627\redstonecircuit\block\mechanism\BlockTNT;
use tedo0627\redstonecircuit\block\mechanism\BlockWoodenDoor;
use tedo0627\redstonecircuit\block\mechanism\BlockWoodenTrapdoor;
use tedo0627\redstonecircuit\block\power\BlockDaylightSensor;
use tedo0627\redstonecircuit\block\power\BlockJukeBox;
use tedo0627\redstonecircuit\block\power\BlockLever;
use tedo0627\redstonecircuit\block\power\BlockObserver;
use tedo0627\redstonecircuit\block\power\BlockRedstone;
use tedo0627\redstonecircuit\block\power\BlockRedstoneTorch;
use tedo0627\redstonecircuit\block\power\BlockStoneButton;
use tedo0627\redstonecircuit\block\power\BlockStonePressurePlate;
use tedo0627\redstonecircuit\block\power\BlockTrappedChest;
use tedo0627\redstonecircuit\block\power\BlockTripwire;
use tedo0627\redstonecircuit\block\power\BlockTripwireHook;
use tedo0627\redstonecircuit\block\power\BlockWeightedPressurePlateHeavy;
use tedo0627\redstonecircuit\block\power\BlockWeightedPressurePlateLight;
use tedo0627\redstonecircuit\block\power\BlockWoodenButton;
use tedo0627\redstonecircuit\block\power\BlockWoodenPressurePlate;
use tedo0627\redstonecircuit\block\transmission\BlockRedstoneComparator;
use tedo0627\redstonecircuit\block\transmission\BlockRedstoneRepeater;
use tedo0627\redstonecircuit\block\transmission\BlockRedstoneWire;
use tedo0627\redstonecircuit\listener\CommandBlockListener;
use tedo0627\redstonecircuit\listener\InventoryListener;
use tedo0627\redstonecircuit\loader\BlockEntityLoader;
use tedo0627\redstonecircuit\loader\BlockLoader;
use tedo0627\redstonecircuit\loader\ItemBlockLoader;
use tedo0627\redstonecircuit\loader\Loader;

class RedstoneCircuit extends PluginBase {

    /** @var Loader[] */
    private array $loader = [];

    public function onLoad(): void {
        // mechanism
        $this->addBlock("command_block", new BlockCommand(new BlockIdentifierFlattened(Ids::COMMAND_BLOCK, [Ids::REPEATING_COMMAND_BLOCK, Ids::CHAIN_COMMAND_BLOCK], 0, null, BlockEntityCommand::class), "Command Block", BlockBreakInfo::indestructible()));
        $this->addBlockEntity("command_block", BlockEntityCommand::class, ["CommandBlock", "minecraft:command_block"]);

        $info = new BlockBreakInfo(3.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
        $this->addBlock("dispenser", new BlockDispenser(new BlockIdentifier(Ids::DISPENSER, 0, null, BlockEntityDispenser::class), "Dispenser", $info));
        $this->addBlockEntity("dispenser", BlockEntityDispenser::class, ["Dispenser", "minecraft:dispenser"]);
        $this->overrideBlock("door", Ids::IRON_DOOR_BLOCK, fn($bid, $name, $info) => new BlockIronDoor($bid, $name, $info));
        $this->overrideBlocks("door", [
            Ids::OAK_DOOR_BLOCK, Ids::SPRUCE_DOOR_BLOCK, Ids::BIRCH_DOOR_BLOCK,
            Ids::JUNGLE_DOOR_BLOCK, Ids::ACACIA_DOOR_BLOCK, Ids::DARK_OAK_DOOR_BLOCK
        ], fn($bid, $name, $info) => new BlockWoodenDoor($bid, $name, $info));
        $this->addBlock("dropper", new BlockDropper(new BlockIdentifier(Ids::DROPPER, 0, null, BlockEntityDropper::class), "Dropper", $info));
        $this->addBlockEntity("dropper", BlockEntityDropper::class, ["Dropper", "minecraft:dropper"]);
        $this->overrideBlocks("fence_gate", [
            Ids::OAK_FENCE_GATE, Ids::SPRUCE_FENCE_GATE, Ids::BIRCH_FENCE_GATE,
            Ids::JUNGLE_FENCE_GATE, Ids::DARK_OAK_FENCE_GATE, Ids::ACACIA_FENCE_GATE
        ], fn($bid, $name, $info) => new BlockFenceGate($bid, $name, $info));
        $this->overrideBlock("hopper", Ids::HOPPER_BLOCK, fn($bid, $name, $info) => new BlockHopper($bid, $name, $info), BlockEntityHopper::class);
        $this->addBlockEntity("hopper", BlockEntityHopper::class, ["Hopper", "minecraft:hopper"]);
        $this->overrideBlock("note_block", Ids::NOTEBLOCK, fn($bid, $name, $info) => new BlockNote($bid, $name, $info), BlockEntityNote::class);
        $this->addBlockEntity("note_block", BlockEntityNote::class, ["Music", "minecraft:noteblock"]);
        $this->overrideBlock("rail", Ids::ACTIVATOR_RAIL, fn($bid, $name, $info) => new BlockActivatorRail($bid, $name, $info));
        $this->overrideBlock("rail", Ids::POWERED_RAIL, fn($bid, $name, $info) => new BlockPoweredRail($bid, $name, $info));
        $this->overrideBlock("redstone_lamp", Ids::REDSTONE_LAMP, fn($bid, $name, $info) => new BlockRedstoneLamp($bid, $name, $info));
        $this->overrideBlock("skull", Ids::SKULL_BLOCK, fn($bid, $name, $info) => new BlockSkull($bid, $name, $info), BlockEntitySkull::class);
        $this->addBlockEntity("skull", BlockEntitySkull::class, ["Skull", "minecraft:skull"]);
        $this->overrideBlock("tnt", Ids::TNT, fn($bid, $name, $info) => new BlockTNT($bid, $name, $info));
        $this->overrideBlock("trapdoor", Ids::IRON_TRAPDOOR, fn($bid, $name, $info) => new BlockIronTrapdoor($bid, $name, $info));
        $this->overrideBlocks("trapdoor", [
            Ids::WOODEN_TRAPDOOR, Ids::ACACIA_TRAPDOOR, Ids::BIRCH_TRAPDOOR,
            Ids::DARK_OAK_TRAPDOOR, Ids::JUNGLE_TRAPDOOR, Ids::SPRUCE_TRAPDOOR
        ], fn($bid, $name, $info) => new BlockWoodenTrapdoor($bid, $name, $info));

        // power
        $this->overrideBlock("button", Ids::STONE_BUTTON, fn($bid, $name, $info) => new BlockStoneButton($bid, $name, $info));
        $this->overrideBlocks("button", [
            Ids::WOODEN_BUTTON, Ids::ACACIA_BUTTON, Ids::BIRCH_BUTTON,
            Ids::DARK_OAK_BUTTON, Ids::JUNGLE_BUTTON, Ids::SPRUCE_BUTTON
        ], fn($bid, $name, $info) => new BlockWoodenButton($bid, $name, $info));
        $this->overrideBlock("daylight_sensor", Ids::DAYLIGHT_SENSOR, fn($bid, $name, $info) => new BlockDaylightSensor($bid, $name, $info));
        $this->overrideBlock("jukebox", Ids::JUKEBOX, fn($bid, $name, $info) => new BlockJukeBox($bid, $name, $info));
        $this->overrideBlock("lever", Ids::LEVER, fn($bid, $name, $info) => new BlockLever($bid, $name, $info));
        $this->addBlock("observer", new BlockObserver(new BlockIdentifier(Ids::OBSERVER, 0, null, BlockEntityObserver::class), "Observer", $info));
        $this->addBlockEntity("observer", BlockEntityObserver::class, ["Observer", "minecraft:observer"]);
        $this->overrideBlock("redstone_block", Ids::REDSTONE_BLOCK, fn($bid, $name, $info) => new BlockRedstone($bid, $name, $info));
        $this->overrideBlock("redstone_torch", Ids::REDSTONE_TORCH, fn($bid, $name, $info) => new BlockRedstoneTorch($bid, $name, $info));
        $this->overrideBlock("pressure_plate", Ids::STONE_PRESSURE_PLATE, fn($bid, $name, $info) => new BlockStonePressurePlate($bid, $name, $info));
        $this->overrideBlock("pressure_plate", Ids::WOODEN_PRESSURE_PLATE, fn($bid, $name, $info) => new BlockWoodenPressurePlate($bid, $name, $info));
        $this->overrideBlock("trapped_chest", Ids::TRAPPED_CHEST, fn($bid, $name, $info) => new BlockTrappedChest($bid, $name, $info), BlockEntityChest::class);
        $this->addBlockEntity("trapped_chest", BlockEntityChest::class, ["Chest", "minecraft:chest"]);
        $this->overrideBlock("tripwire", Ids::TRIPWIRE, fn($bid, $name, $info) => new BlockTripwire($bid, $name, $info));
        $this->overrideBlock("tripwire", Ids::TRIPWIRE_HOOK, fn($bid, $name, $info) => new BlockTripwireHook($bid, $name, $info));
        $this->addItemBlock("tripwire", Ids::TRIPWIRE, new ItemIdentifier(ItemIds::STRING, 0));
        $this->overrideBlock("weighted_pressure_plate", Ids::HEAVY_WEIGHTED_PRESSURE_PLATE, fn($bid, $name, $info) => new BlockWeightedPressurePlateHeavy($bid, $name, $info));
        $this->overrideBlock("weighted_pressure_plate", Ids::LIGHT_WEIGHTED_PRESSURE_PLATE, fn($bid, $name, $info) => new BlockWeightedPressurePlateLight($bid, $name, $info));

        // transmission
        $this->overrideBlock("comparator", Ids::UNPOWERED_COMPARATOR, fn($bid, $name, $info) => new BlockRedstoneComparator($bid, $name, $info));
        $this->overrideBlock("redstone_wire", Ids::REDSTONE_WIRE, fn($bid, $name, $info) => new BlockRedstoneWire($bid, $name, $info));
        $this->addItemBlock("redstone_wire", Ids::REDSTONE_WIRE, new ItemIdentifier(ItemIds::REDSTONE, 0));
        $this->overrideBlock("repeater", Ids::UNPOWERED_REPEATER, fn($bid, $name, $info) => new BlockRedstoneRepeater($bid, $name, $info));

        $this->load();
    }

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents(new CommandBlockListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new InventoryListener(), $this);
    }

    private function overrideBlock(string $name, int $id, Closure $callback, ?string $class = null): void {
        $this->loader[] = BlockLoader::createBlock($name, $id, $callback, $class);
    }

    private function overrideBlocks(string $name, array $ids, Closure $callback, ?string $class = null): void {
        foreach ($ids as $id) $this->overrideBlock($name, $id, $callback, $class);
    }

    private function addBlock(string $name, Block $block): void {
        $this->loader[] = new BlockLoader($name, $block);
    }

    private function addItemBlock(string $name, int $blockId, ItemIdentifier $identifier): void {
        $this->loader[] = new ItemBlockLoader($name, $blockId, $identifier);
    }

    private function addBlockEntity(string $name, string $className, array $saveNames): void {
        $this->loader[] = new BlockEntityLoader($name, $className, $saveNames);
    }

    private function load(): void {
        $config = $this->getConfig();
        for ($i = 0; $i < count($this->loader); $i++) {
            $loader = $this->loader[$i];
            if ($config->getNested("blocks." . $loader->getName(), true)) $loader->load();
        }
    }
}