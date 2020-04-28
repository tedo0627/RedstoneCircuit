<?php

namespace redstone\utils;

use pocketmine\block\Block;
use const pocketmine\RESOURCE_PATH;
use function file_get_contents;
use function json_decode;

class GlobalBlockPalette {

    private $nameTable = [];
    private $idTable = [];

    public function __construct() {
        $legacyIdMap = json_decode(file_get_contents(RESOURCE_PATH . "vanilla/block_id_map.json"), true);
        foreach($legacyIdMap as $name => $id){
            $id = $legacyIdMap[$name];
            $this->nameTable[$name] = $id;
            $this->idTable[$id] = $name;
        }
    }

    public function getId(string $name) : int {
        return $this->nameTable[$name];
    }

    public function getBlock(string $name, int $damage) : Block {
        return Block::get($this->getId($name), $damage);
    }

    public function getName(int $id) : string {
        return $this->idTable[$id];
    }

    public function getNameAt(Block $block) : string {
        return $this->idTable[$block->getId()];
    }
}