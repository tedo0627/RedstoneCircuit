<?php

namespace tedo0627\redstonecircuit\block;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\utils\SingletonTrait;
use const pocketmine\BEDROCK_DATA_PATH;

class BlockTable {
    use SingletonTrait;

    /** @var array<int, string> */
    private array $idToName = [];
    /** @var array<string, int> */
    private array $nameToId = [];

    /** @var array<int, array<int, CompoundTag>> */
    private array $idToDamageToState = [];
    /** @var array<int, array<String, int>> */
    private array $idToStateToDamage = [];

    public function __construct() {
        $json = json_decode(file_get_contents(BEDROCK_DATA_PATH . "block_id_map.json"), true);
        foreach ($json as $name => $id) {
            $this->idToName[$id] = $name;
            $this->nameToId[$name] = $id;
        }

        $mapping = RuntimeBlockMapping::getInstance();
        $idCheck = -1;
        $damage = 0;
        foreach ($mapping->getBedrockKnownStates() as $tag) {
            $name = $tag->getString("name");
            if (!$this->existsId($name)) continue;

            $id = $this->getId($name);
            if ($id === $idCheck) {
                $damage++;
            } else {
                $damage = 0;
                $idCheck = $id;
            }
            if (!array_key_exists($id, $this->idToDamageToState)) $this->idToDamageToState[$id] = [];
            if (!array_key_exists($id, $this->idToStateToDamage)) $this->idToStateToDamage[$id] = [];
            $states = $tag->getCompoundTag("states");
            $this->idToDamageToState[$id][$damage] = $states;
            $this->idToStateToDamage[$id][$states->toString()] = $damage;
        }
    }

    public function existsName(int $id): bool {
        return array_key_exists($id, $this->idToName);
    }

    public function getName(int $id): string {
        return $this->idToName[$id];
    }

    public function existsId(string $name): bool {
        return array_key_exists($name, $this->nameToId);
    }

    public function getId(string $name): int {
        return $this->nameToId[$name];
    }

    public function getStates(int $id, int $damage): CompoundTag {
        return $this->idToDamageToState[$id][$damage];
    }

    public function getDamage(int $id, CompoundTag $states): int {
        return $this->idToStateToDamage[$id][$states->toString()];
    }
}