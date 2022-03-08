<?php

namespace tedo0627\redstonecircuit\loader;

use pocketmine\block\BlockFactory;
use pocketmine\inventory\CreativeInventory;
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
        $item = new ItemBlock($this->identifier, BlockFactory::getInstance()->get($this->blockId, 0));
        ItemFactory::getInstance()->register($item, true);

        $creative = CreativeInventory::getInstance();
        if (!$creative->contains($item)) $creative->add($item);
    }
}