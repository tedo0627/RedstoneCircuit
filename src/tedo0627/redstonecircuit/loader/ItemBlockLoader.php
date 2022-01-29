<?php

namespace tedo0627\redstonecircuit\loader;

use pocketmine\block\BlockFactory;
use pocketmine\item\ItemBlock;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;

class ItemBlockLoader extends Loader {

    private int $blockId;
    private ItemIdentifier $identifier;

    public function __construct(string $name, int $blockId, ItemIdentifier $identifier) {
        parent::__construct($name);

        $this->blockId = $blockId;
        $this->identifier = $identifier;
    }

    public function load(): void {
        ItemFactory::getInstance()->register(new ItemBlock($this->identifier, BlockFactory::getInstance()->get($this->blockId, 0)), true);
    }
}