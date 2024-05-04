<?php

namespace tedo0627\redstonecircuit\registry;

use pocketmine\block\Lever;
use pocketmine\block\RedstoneComparator;
use pocketmine\block\RedstoneLamp;
use pocketmine\block\RedstoneRepeater;
use pocketmine\block\RedstoneTorch;
use pocketmine\block\RedstoneWire;
use pocketmine\block\StoneButton;
use pocketmine\block\utils\LeverFacing;
use pocketmine\block\WoodenButton;
use pocketmine\data\bedrock\block\BlockStateNames as SNames;
use pocketmine\data\bedrock\block\BlockStateStringValues;
use pocketmine\data\bedrock\block\BlockTypeNames as BNames;
use pocketmine\data\bedrock\block\convert\BlockStateDeserializerHelper;
use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateSerializerHelper;
use pocketmine\data\bedrock\block\convert\BlockStateWriter as Writer;
use tedo0627\redstonecircuit\registry\BlockRegistryHelper as Helper;
use tedo0627\redstonecircuit\registry\RedstoneComponent as Blocks;
use pmmp\thread\ThreadSafeArray;
use Closure;

class BlockLoader {

    /**
     * @var array<string, Closure> $loader
     */
    private array $loader = [];

    public function setup(): void {
        $this->setupMechanism();
        $this->setupPower();
        $this->setupTransmission();
    }

    private function setupMechanism(): void {
        $this->add("redstone_lamp", function () {
            Helper::runtime(Blocks::REDSTONE_LAMP());
            Helper::serialize(Blocks::REDSTONE_LAMP(), fn(RedstoneLamp $block) => new Writer($block->isPowered() ? BNames::LIT_REDSTONE_LAMP : BNames::REDSTONE_LAMP));
            Helper::deserialize(BNames::LIT_REDSTONE_LAMP, fn() => Blocks::REDSTONE_LAMP()->setPowered(true));
            Helper::deserialize(BNames::REDSTONE_LAMP, fn() => Blocks::REDSTONE_LAMP()->setPowered(false));
            Helper::stringToItemParser(["active_redstone_lamp", "inactive_redstone_lamp", "lit_redstone_lamp", "redstone_lamp"], Blocks::REDSTONE_COMPARATOR());
        });
    }

    private function setupPower(): void {
        $this->add("lever", function () {
            Helper::runtime(Blocks::LEVER());
            Helper::serialize(Blocks::LEVER(), fn(Lever $block) => Writer::create(BNames::LEVER)
                ->writeBool(SNames::OPEN_BIT, $block->isActivated())
                ->writeString(SNames::LEVER_DIRECTION, match($block->getFacing()){
                    LeverFacing::DOWN_AXIS_Z => BlockStateStringValues::LEVER_DIRECTION_DOWN_NORTH_SOUTH,
                    LeverFacing::DOWN_AXIS_X => BlockStateStringValues::LEVER_DIRECTION_DOWN_EAST_WEST,
                    LeverFacing::UP_AXIS_Z => BlockStateStringValues::LEVER_DIRECTION_UP_NORTH_SOUTH,
                    LeverFacing::UP_AXIS_X => BlockStateStringValues::LEVER_DIRECTION_UP_EAST_WEST,
                    LeverFacing::NORTH => BlockStateStringValues::LEVER_DIRECTION_NORTH,
                    LeverFacing::SOUTH => BlockStateStringValues::LEVER_DIRECTION_SOUTH,
                    LeverFacing::WEST => BlockStateStringValues::LEVER_DIRECTION_WEST,
                    LeverFacing::EAST => BlockStateStringValues::LEVER_DIRECTION_EAST,
                })
            );
            Helper::deserialize(BNames::LEVER, fn(BlockStateReader $in) => Blocks::LEVER()
                ->setActivated($in->readBool(SNames::OPEN_BIT))
                ->setFacing(match($value = $in->readString(SNames::LEVER_DIRECTION)){
                    BlockStateStringValues::LEVER_DIRECTION_DOWN_NORTH_SOUTH => LeverFacing::DOWN_AXIS_Z,
                    BlockStateStringValues::LEVER_DIRECTION_DOWN_EAST_WEST => LeverFacing::DOWN_AXIS_X,
                    BlockStateStringValues::LEVER_DIRECTION_UP_NORTH_SOUTH => LeverFacing::UP_AXIS_Z,
                    BlockStateStringValues::LEVER_DIRECTION_UP_EAST_WEST => LeverFacing::UP_AXIS_X,
                    BlockStateStringValues::LEVER_DIRECTION_NORTH => LeverFacing::NORTH,
                    BlockStateStringValues::LEVER_DIRECTION_SOUTH => LeverFacing::SOUTH,
                    BlockStateStringValues::LEVER_DIRECTION_WEST => LeverFacing::WEST,
                    BlockStateStringValues::LEVER_DIRECTION_EAST => LeverFacing::EAST,
                    default => throw $in->badValueException(SNames::LEVER_DIRECTION, $value),
                })
            );
            Helper::stringToItemParser("lever", Blocks::LEVER());
            Helper::vanillaBlocks("lever", Blocks::LEVER());
        });
        $this->add("redstone", function () {
            Helper::runtime(Blocks::REDSTONE());
            Helper::serialize(Blocks::REDSTONE(), fn() => new Writer(BNames::REDSTONE_BLOCK));
            Helper::deserialize(BNames::REDSTONE_BLOCK, fn() => Blocks::REDSTONE());
            Helper::stringToItemParser("redstone_block", Blocks::REDSTONE());
            Helper::vanillaBlocks("redstone", Blocks::REDSTONE());
        });
        $this->add("redstone_torch", function () {
            Helper::runtime(Blocks::REDSTONE_TORCH());
            Helper::serialize(Blocks::REDSTONE_TORCH(), fn(RedstoneTorch $block) => Writer::create($block->isLit() ? BNames::REDSTONE_TORCH : BNames::UNLIT_REDSTONE_TORCH)
                ->writeTorchFacing($block->getFacing())
            );
            Helper::deserialize(BNames::REDSTONE_TORCH, fn(BlockStateReader $in) => Blocks::REDSTONE_TORCH()
                ->setFacing($in->readTorchFacing())
                ->setLit(true)
            );
            Helper::deserialize(BNames::UNLIT_REDSTONE_TORCH, fn(BlockStateReader $in) => Blocks::REDSTONE_TORCH()
                ->setFacing($in->readTorchFacing())
                ->setLit(false)
            );
            Helper::stringToItemParser(["lit_redstone_torch", "redstone_torch", "unlit_redstone_torch"], Blocks::REDSTONE_TORCH());
            Helper::vanillaBlocks("redstone_torch", Blocks::REDSTONE_TORCH());
        });
        $this->add("stone_button", function () {
            $blocks = [Blocks::POLISHED_BLACKSTONE_BUTTON(), Blocks::STONE_BUTTON()];
            $names = [BNames::POLISHED_BLACKSTONE_BUTTON, BNames::STONE_BUTTON];
            $alias = ["polished_blackstone_button", "stone_button"];
            for ($i = 0; $i < count($blocks); $i++) {
                Helper::runtime(clone $blocks[$i]);
                Helper::serialize(clone $blocks[$i], fn(StoneButton $block) => BlockStateSerializerHelper::encodeButton(clone $block, new Writer($names[$i])));
                Helper::deserialize($names[$i], fn(BlockStateReader $in) => BlockStateDeserializerHelper::decodeButton(clone $blocks[$i], $in));
                Helper::stringToItemParser($alias[$i], clone $blocks[$i]);
                Helper::vanillaBlocks($alias[$i], clone $blocks[$i]);
            }
        });
        $this->add("wooden_button", function () {
            $blocks = [
                Blocks::ACACIA_BUTTON(), Blocks::BIRCH_BUTTON(), Blocks::CHERRY_BUTTON(), Blocks::CRIMSON_BUTTON(),
                Blocks::DARK_OAK_BUTTON(), Blocks::JUNGLE_BUTTON(), Blocks::MANGROVE_BUTTON(), Blocks::OAK_BUTTON(),
                Blocks::SPRUCE_BUTTON(), Blocks::WARPED_BUTTON()
            ];
            $names = [
                BNames::ACACIA_BUTTON, BNames::BIRCH_BUTTON, BNames::CHERRY_BUTTON, BNames::CRIMSON_BUTTON,
                BNames::DARK_OAK_BUTTON, BNames::JUNGLE_BUTTON, BNames::MANGROVE_BUTTON, BNames::WOODEN_BUTTON,
                BNames::SPRUCE_BUTTON, BNames::WARPED_BUTTON
            ];
            $alias = array_map(fn(string $str) => str_replace("minecraft:", "", $str), $names);
            for ($i = 0; $i < count($blocks); $i++) {
                Helper::runtime(clone $blocks[$i]);
                Helper::serialize(clone $blocks[$i], fn(WoodenButton $block) => BlockStateSerializerHelper::encodeButton(clone $block, new Writer($names[$i])));
                Helper::deserialize($names[$i], fn(BlockStateReader $in) => BlockStateDeserializerHelper::decodeButton(clone $blocks[$i], $in));
                Helper::stringToItemParser($alias[$i], clone $blocks[$i]);
                Helper::vanillaBlocks($alias[$i], clone $blocks[$i]);
            }
        });
    }

    private function setupTransmission(): void {
        $this->add("redstone_comparator", function () {
            Helper::runtime(Blocks::REDSTONE_COMPARATOR());
            Helper::serialize(Blocks::REDSTONE_COMPARATOR(), fn(RedstoneComparator $block) => Writer::create($block->isPowered() ? BNames::POWERED_COMPARATOR : BNames::UNPOWERED_COMPARATOR)
                ->writeBool(SNames::OUTPUT_LIT_BIT, $block->isPowered())
                ->writeBool(SNames::OUTPUT_SUBTRACT_BIT, $block->isSubtractMode())
                ->writeCardinalHorizontalFacing($block->getFacing())
            );
            Helper::deserialize(BNames::POWERED_COMPARATOR, fn(BlockStateReader $in) => BlockStateDeserializerHelper::decodeComparator(Blocks::REDSTONE_COMPARATOR(), $in)->setPowered(true));
            Helper::deserialize(BNames::UNPOWERED_COMPARATOR, fn(BlockStateReader $in) => BlockStateDeserializerHelper::decodeComparator(Blocks::REDSTONE_COMPARATOR(), $in)->setPowered(false));
            Helper::stringToItemParser(["comparator", "comparator_block", "redstone_comparator", "powered_comparator", "powered_comparator_block", "unpowered_comparator", "unpowered_comparator_block"], Blocks::REDSTONE_COMPARATOR());
        });

        $this->add("redstone_repeater", function () {
            Helper::runtime(Blocks::REDSTONE_REPEATER());
            Helper::serialize(Blocks::REDSTONE_REPEATER(), fn(RedstoneRepeater $block) => Writer::create($block->isPowered() ? BNames::POWERED_REPEATER : BNames::UNPOWERED_REPEATER)
                ->writeCardinalHorizontalFacing($block->getFacing())
                ->writeInt(SNames::REPEATER_DELAY, $block->getDelay() - 1)
            );
            Helper::deserialize(BNames::POWERED_REPEATER, fn(BlockStateReader $in) => BlockStateDeserializerHelper::decodeRepeater(Blocks::REDSTONE_REPEATER(), $in)->setPowered(true));
            Helper::deserialize(BNames::UNPOWERED_REPEATER, fn(BlockStateReader $in) => BlockStateDeserializerHelper::decodeRepeater(Blocks::REDSTONE_REPEATER(), $in)->setPowered(false));
            Helper::stringToItemParser(["powered_repeater", "powered_repeater_block", "redstone_repeater", "repeater", "repeater_block", "unpowered_repeater", "unpowered_repeater_block"], Blocks::REDSTONE_REPEATER());
        });

        $this->add("redstone_wire", function () {
            Helper::runtime(Blocks::REDSTONE_WIRE());
            Helper::serialize(Blocks::REDSTONE_WIRE(), fn(RedstoneWire $block) => Writer::create(BNames::REDSTONE_WIRE)
                ->writeInt(SNames::REDSTONE_SIGNAL, $block->getOutputSignalStrength())
            );
            Helper::deserialize(Blocks::REDSTONE_WIRE(), fn(BlockStateReader $in) => Blocks::REDSTONE_WIRE()
                ->setOutputSignalStrength($in->readBoundedInt(SNames::REDSTONE_SIGNAL, 0, 15))
            );
            Helper::stringToItemParser("redstone_wire", Blocks::REDSTONE_WIRE());
            Helper::vanillaBlocks("redstone_wire", Blocks::REDSTONE_WIRE());
        });
    }

    /**
     * @param string $name Name when reading from config.
     * @param Closure $closure What to execute when registering a block.
     */
    private function add(string $name, Closure $closure): void {
        $this->loader[$name] = $closure;
    }

    public function load(ThreadSafeArray $blockConfig): void {
        foreach ($this->loader as $key => $value) {
            $check = $blockConfig[$key];
            if ($check === true || $check === null) {
                $value();
            }
        }
    }
}