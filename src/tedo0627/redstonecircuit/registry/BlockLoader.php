<?php

namespace tedo0627\redstonecircuit\registry;

use pocketmine\block\RedstoneWire;
use pocketmine\data\bedrock\block\BlockStateNames as SNames;
use pocketmine\data\bedrock\block\BlockTypeNames as BNames;
use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter as Writer;
use tedo0627\redstonecircuit\registry\BlockRegistryHelper as Helper;
use tedo0627\redstonecircuit\registry\RedstoneComponent as Blocks;
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

    }

    private function setupPower(): void {
        $this->add("redstone", function () {
            Helper::runtime(Blocks::REDSTONE());
            Helper::serialize(Blocks::REDSTONE(), fn() => new Writer(BNames::REDSTONE_BLOCK));
            Helper::deserialize(BNames::REDSTONE_BLOCK, fn() => Blocks::REDSTONE());
            Helper::stringToItemParser("redstone_block", Blocks::REDSTONE());
            Helper::vanillaBlocks("redstone", Blocks::REDSTONE());
        });
    }

    private function setupTransmission(): void {
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
            //Helper::item(ItemTypeNames::REDSTONE, Blocks::REDSTONE_WIRE());
        });
    }

    /**
     * @param string $name Name when reading from config.
     * @param Closure $closure What to execute when registering a block.
     */
    private function add(string $name, Closure $closure): void {
        $this->loader[$name] = $closure;
    }

    public function load(): void {
        foreach ($this->loader as $key => $value) {
            $value(); // TODO
        }
    }
}