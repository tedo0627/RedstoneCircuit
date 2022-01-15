<?php

namespace tedo0627\redstonecircuit;

use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockLegacyIds as Ids;
use pocketmine\block\BlockToolType;
use pocketmine\block\tile\TileFactory;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\ToolTier;
use pocketmine\plugin\PluginBase;
use tedo0627\redstonecircuit\block\entity\BlockEntityChest;
use tedo0627\redstonecircuit\block\entity\BlockEntityDispenser;
use tedo0627\redstonecircuit\block\entity\BlockEntityDropper;
use tedo0627\redstonecircuit\block\entity\BlockEntityNote;
use tedo0627\redstonecircuit\block\entity\BlockEntitySkull;
use tedo0627\redstonecircuit\block\mechanism\BlockDispenser;
use tedo0627\redstonecircuit\block\mechanism\BlockDropper;
use tedo0627\redstonecircuit\block\mechanism\BlockFenceGate;
use tedo0627\redstonecircuit\block\mechanism\BlockIronDoor;
use tedo0627\redstonecircuit\block\mechanism\BlockIronTrapdoor;
use tedo0627\redstonecircuit\block\mechanism\BlockNote;
use tedo0627\redstonecircuit\block\mechanism\BlockRedstoneLamp;
use tedo0627\redstonecircuit\block\mechanism\BlockSkull;
use tedo0627\redstonecircuit\block\mechanism\BlockTNT;
use tedo0627\redstonecircuit\block\mechanism\BlockWoodenDoor;
use tedo0627\redstonecircuit\block\mechanism\BlockWoodenTrapdoor;
use tedo0627\redstonecircuit\block\power\BlockLever;
use tedo0627\redstonecircuit\block\power\BlockRedstone;
use tedo0627\redstonecircuit\block\power\BlockRedstoneTorch;
use tedo0627\redstonecircuit\block\power\BlockStoneButton;
use tedo0627\redstonecircuit\block\power\BlockStonePressurePlate;
use tedo0627\redstonecircuit\block\power\BlockTrappedChest;
use tedo0627\redstonecircuit\block\power\BlockWeightedPressurePlateHeavy;
use tedo0627\redstonecircuit\block\power\BlockWeightedPressurePlateLight;
use tedo0627\redstonecircuit\block\power\BlockWoodenButton;
use tedo0627\redstonecircuit\block\power\BlockWoodenPressurePlate;
use tedo0627\redstonecircuit\block\transmission\BlockRedstoneComparator;
use tedo0627\redstonecircuit\block\transmission\BlockRedstoneRepeater;
use tedo0627\redstonecircuit\block\transmission\BlockRedstoneWire;
use tedo0627\redstonecircuit\item\ItemRedstone;
use tedo0627\redstonecircuit\listener\InventoryListener;

class RedstoneCircuit extends PluginBase {

    public function onLoad(): void {
        $factory = BlockFactory::getInstance();

        // mechanism
        $bid = new BlockIdentifier(Ids::DISPENSER, 0, null, BlockEntityDispenser::class);
        $info = new BlockBreakInfo(3.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
        $factory->register(new BlockDispenser($bid, "Dispenser", $info));
        $bid = new BlockIdentifier(Ids::DROPPER, 0, null, BlockEntityDropper::class);
        $factory->register(new BlockDropper($bid, "Dropper", $info));
        $this->registerBlocks([
            Ids::OAK_FENCE_GATE, Ids::SPRUCE_FENCE_GATE, Ids::BIRCH_FENCE_GATE,
            Ids::JUNGLE_FENCE_GATE, Ids::DARK_OAK_FENCE_GATE, Ids::ACACIA_FENCE_GATE
        ], fn($bid, $name, $info) => new BlockFenceGate($bid, $name, $info));
        $this->registerBlock(Ids::IRON_DOOR_BLOCK, fn($bid, $name, $info) => new BlockIronDoor($bid, $name, $info));
        $this->registerBlock(Ids::IRON_TRAPDOOR, fn($bid, $name, $info) => new BlockIronTrapdoor($bid, $name, $info));
        $this->registerBlock(Ids::NOTEBLOCK, fn($bid, $name, $info) => new BlockNote($bid, $name, $info), BlockEntityNote::class);
        $this->registerBlock(Ids::REDSTONE_LAMP, fn($bid, $name, $info) => new BlockRedstoneLamp($bid, $name, $info));
        $this->registerBlock(Ids::SKULL_BLOCK, fn($bid, $name, $info) => new BlockSkull($bid, $name, $info), BlockEntitySkull::class);
        $this->registerBlock(Ids::TNT, fn($bid, $name, $info) => new BlockTNT($bid, $name, $info));
        $this->registerBlocks([
            Ids::OAK_DOOR_BLOCK, Ids::SPRUCE_DOOR_BLOCK, Ids::BIRCH_DOOR_BLOCK,
            Ids::JUNGLE_DOOR_BLOCK, Ids::ACACIA_DOOR_BLOCK, Ids::DARK_OAK_DOOR_BLOCK
        ], fn($bid, $name, $info) => new BlockWoodenDoor($bid, $name, $info));
        $this->registerBlocks([
            Ids::WOODEN_TRAPDOOR, Ids::ACACIA_TRAPDOOR, Ids::BIRCH_TRAPDOOR,
            Ids::DARK_OAK_TRAPDOOR, Ids::JUNGLE_TRAPDOOR, Ids::SPRUCE_TRAPDOOR
        ], fn($bid, $name, $info) => new BlockWoodenTrapdoor($bid, $name, $info));

        // power
        $this->registerBlock(Ids::LEVER, fn($bid, $name, $info) => new BlockLever($bid, $name, $info));
        $this->registerBlock(Ids::REDSTONE_BLOCK, fn($bid, $name, $info) => new BlockRedstone($bid, $name, $info));
        $this->registerBlock(Ids::REDSTONE_TORCH, fn($bid, $name, $info) => new BlockRedstoneTorch($bid, $name, $info));
        $this->registerBlock(Ids::STONE_BUTTON, fn($bid, $name, $info) => new BlockStoneButton($bid, $name, $info));
        $this->registerBlock(Ids::STONE_PRESSURE_PLATE, fn($bid, $name, $info) => new BlockStonePressurePlate($bid, $name, $info));
        $this->registerBlock(Ids::TRAPPED_CHEST, fn($bid, $name, $info) => new BlockTrappedChest($bid, $name, $info), BlockEntityChest::class);
        $this->registerBlock(Ids::HEAVY_WEIGHTED_PRESSURE_PLATE, fn($bid, $name, $info) => new BlockWeightedPressurePlateHeavy($bid, $name, $info));
        $this->registerBlock(Ids::LIGHT_WEIGHTED_PRESSURE_PLATE, fn($bid, $name, $info) => new BlockWeightedPressurePlateLight($bid, $name, $info));
        $this->registerBlocks([
            Ids::WOODEN_BUTTON, Ids::ACACIA_BUTTON, Ids::BIRCH_BUTTON,
            Ids::DARK_OAK_BUTTON, Ids::JUNGLE_BUTTON, Ids::SPRUCE_BUTTON
        ], fn($bid, $name, $info) => new BlockWoodenButton($bid, $name, $info));
        $this->registerBlock(Ids::WOODEN_PRESSURE_PLATE, fn($bid, $name, $info) => new BlockWoodenPressurePlate($bid, $name, $info));

        // transmission
        $this->registerBlock(Ids::UNPOWERED_COMPARATOR, fn($bid, $name, $info) => new BlockRedstoneComparator($bid, $name, $info));
        $this->registerBlock(Ids::UNPOWERED_REPEATER, fn($bid, $name, $info) => new BlockRedstoneRepeater($bid, $name, $info));
        $this->registerBlock(Ids::REDSTONE_WIRE, fn($bid, $name, $info) => new BlockRedstoneWire($bid, $name, $info));

        ItemFactory::getInstance()->register(new ItemRedstone(new ItemIdentifier(ItemIds::REDSTONE, 0), "Redstone"), true);

        TileFactory::getInstance()->register(BlockEntityNote::class, ["Music", "minecraft:noteblock"]);
        TileFactory::getInstance()->register(BlockEntitySkull::class, ["Skull", "minecraft:skull"]);
        TileFactory::getInstance()->register(BlockEntityChest::class, ["Chest", "minecraft:chest"]);
        TileFactory::getInstance()->register(BlockEntityDispenser::class, ["Dispenser", "minecraft:dispenser"]);
        TileFactory::getInstance()->register(BlockEntityDropper::class, ["Dropper", "minecraft:dropper"]);
    }

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents(new InventoryListener(), $this);
    }

    private function registerBlock(int $id, callable $callback, ?string $class = null): void {
        $factory = BlockFactory::getInstance();
        $oldBlock = $factory->get($id, 0);
        $bid = $oldBlock->getIdInfo();
        if ($class !== null) {
            $bid = new BlockIdentifier($bid->getBlockId(), $bid->getVariant(), $bid->getItemId(), $class);
        }
        $block = $callback($bid, $oldBlock->getName(), $oldBlock->getBreakInfo());
        $factory->register($block, true);
    }

    private function registerBlocks(array $ids, callable $callback): void {
        $factory = BlockFactory::getInstance();
        foreach ($ids as $id) {
            $oldBlock = $factory->get($id, 0);
            $block = $callback($oldBlock->getIdInfo(), $oldBlock->getName(), $oldBlock->getBreakInfo());
            $factory->register($block, true);
        }
    }
}