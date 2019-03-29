<?php

namespace redstone\utils;

use pocketmine\block\Block;

class GlobalBlockPalette {

    private $nameTable = [];
    private $idTable = [];

    public function __construct() {
		$runtimeIdMap = json_decode(file_get_contents(\pocketmine\RESOURCE_PATH . "runtimeid_table.json"), true);
		$legacyIdMap = json_decode(file_get_contents(\pocketmine\RESOURCE_PATH . "legacy_id_map.json"), true);
		foreach($runtimeIdMap as $k => $obj){
            $name = $obj["name"];
			if(!isset($legacyIdMap[$name])){
				continue;
			}
            $id = $legacyIdMap[$name];
            $damage = $obj["data"];

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