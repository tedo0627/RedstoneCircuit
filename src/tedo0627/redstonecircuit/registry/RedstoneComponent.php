<?php

namespace tedo0627\redstonecircuit\registry;

use Closure;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\CloningRegistryTrait;
use ReflectionClass;
use tedo0627\redstonecircuit\block\mechanism\BlockRedstoneLamp;
use tedo0627\redstonecircuit\block\power\BlockLever;
use tedo0627\redstonecircuit\block\power\BlockRedstone;
use tedo0627\redstonecircuit\block\power\BlockRedstoneTorch;
use tedo0627\redstonecircuit\block\transmission\BlockRedstoneComparator;
use tedo0627\redstonecircuit\block\transmission\BlockRedstoneRepeater;
use tedo0627\redstonecircuit\block\transmission\BlockRedstoneWire;

/**
 * mechanism
 * @method static BlockRedstoneLamp REDSTONE_LAMP()
 *
 * power
 * @method static BlockLever LEVER()
 * @method static BlockRedstone REDSTONE()
 * @method static BlockRedstoneTorch REDSTONE_TORCH()
 *
 * transmission
 * @method static BlockRedstoneComparator REDSTONE_COMPARATOR()
 * @method static BlockRedstoneRepeater REDSTONE_REPEATER()
 * @method static BlockRedstoneWire REDSTONE_WIRE()
 */
class RedstoneComponent {
    use CloningRegistryTrait;

    private function __construct() {
        //NOOP
    }

    protected static function register(string $name, Block $block): void {
        self::_registryRegister($name, $block);
    }

    /**
     * @return Block[]
     * @phpstan-return array<string, Block>
     */
    public static function getAll(): array {
        //phpstan doesn't support generic traits yet :(
        /** @var Block[] $result */
        $result = self::_registryGetAll();
        return $result;
    }

    protected static function setup(): void {
        VanillaBlocks::getAll(); // init VanillaBlocks
        /*
        $targetTypeId = BlockTypeIds::newId();
        self::register("target", new Opaque(new BlockIdentifier($targetTypeId), "Target", new BlockTypeInfo(BlockBreakInfo::instant())));
        */
        self::setupMechanism();
        self::setupPower();
        self::setupTransmission();
    }

    private static function setupMechanism(): void {
        self::registerCallback("redstone_lamp", fn($id, $name, $type) => new BlockRedstoneLamp($id, $name, $type));
    }

    private static function setupPower(): void {
        self::registerCallback("lever", fn($id, $name, $type) => new BlockLever($id, $name, $type));
        self::registerCallback("redstone", fn($id, $name, $type) => new BlockRedstone($id, $name, $type));
        self::registerCallback("redstone_torch", fn($id, $name, $type) => new BlockRedstoneTorch($id, $name, $type));
    }

    private static function setupTransmission(): void {
        self::registerCallback("redstone_comparator", fn($id, $name, $type) => new BlockRedstoneComparator($id, $name, $type));
        self::registerCallback("redstone_repeater", fn($id, $name, $type) => new BlockRedstoneRepeater($id, $name, $type));
        self::registerCallback("redstone_wire", fn($id, $name, $type) => new BlockRedstoneWire($id, $name, $type));
    }

    /**
     * Register a block using the information of the block registered in VanillaBlocks
     *
     * @param string $name Name to be registered. Must be with the name registered with VanillaBlocks.
     * @param Closure $closure Closure that returns an instance of the block to be registered.
     *
     * @see VanillaBlocks::$members
     */
    private static function registerCallback(string $name, Closure $closure): void {
        $upperName = mb_strtoupper($name);

        $reflection = new ReflectionClass(VanillaBlocks::class);
        $property = $reflection->getProperty("members");

        /** @var Block[] $value */
        $value = $property->getValue();
        $block = $value[$upperName];

        $id = $block->getIdInfo();
        $blockName = $block->getName();
        $type = new BlockTypeInfo($block->getBreakInfo(), $block->getTypeTags(), $block->getEnchantmentTags());

        $newBlock = $closure($id, $blockName, $type);

        self::register($name, $newBlock);
    }
}