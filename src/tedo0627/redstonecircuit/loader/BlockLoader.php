<?php

namespace tedo0627\redstonecircuit\loader;

use Closure;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIdentifier;

class BlockLoader extends Loader {

    private Block $block;

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

    public function __construct(string $name, Block $block) {
        parent::__construct($name);

        $this->block = $block;
    }

    public function load(): void {
        BlockFactory::getInstance()->register($this->block, true);
    }
}