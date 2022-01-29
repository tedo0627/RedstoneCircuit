<?php

namespace tedo0627\redstonecircuit\loader;

use pocketmine\block\tile\TileFactory;

class BlockEntityLoader extends Loader {

    private string $className;
    private array $saveNames;

    public function __construct(string $name, string $className, array $saveNames) {
        parent::__construct($name);

        $this->className = $className;
        $this->saveNames = $saveNames;
    }

    public function load(): void {
        TileFactory::getInstance()->register($this->className, $this->saveNames);
    }
}