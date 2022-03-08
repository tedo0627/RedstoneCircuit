<?php

namespace tedo0627\redstonecircuit\loader;

use Closure;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIdentifier;
use pocketmine\inventory\CreativeInventory;

class BlockLoader extends Loader {

    private Block $block;
    private bool $addCreative;

    public static function createBlock(string $name, int $id, Closure $callback, ?string $class = null): self {
        $factory = BlockFactory::getInstance();
        $oldBlock = $factory->get($id, 0);
        $bid = $oldBlock->getIdInfo();
        if ($class !== null) {
            $bid = new BlockIdentifier($bid->getBlockId(), $bid->getVariant(), $bid->getItemId(), $class);
        }
        $block = $callback($bid, $oldBlock->getName(), $oldBlock->getBreakInfo());

        return new self($name, $block);
    }

    public function __construct(string $name, Block $block, bool $addCreative = false) {
        parent::__construct($name);

        $this->block = $block;
        $this->addCreative = $addCreative;
    }

    public function load(): void {
        BlockFactory::getInstance()->register($this->block, true);
        if ($this->addCreative) CreativeInventory::getInstance()->add($this->block->asItem());
    }
}