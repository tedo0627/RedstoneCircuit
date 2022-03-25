<?php

namespace tedo0627\redstonecircuit\block;

use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds as Ids;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\utils\SingletonTrait;
use ReflectionClass;
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
        $reflection = new ReflectionClass(RuntimeBlockMapping::class);
        $property = $reflection->getProperty("runtimeToLegacyMap");
        $property->setAccessible(true);
        $runtimeToLegacyMap = $property->getValue($mapping);
        if (!is_array($runtimeToLegacyMap)) return;

        $idCheck = -1;
        $damage = 0;
        $remap = [7 => 0, 15 => 8];
        $remapIds = [Ids::DISPENSER, Ids::STICKY_PISTON, Ids::PISTON, Ids::PISTONARMCOLLISION, Ids::DROPPER, Ids::OBSERVER];
        foreach ($mapping->getBedrockKnownStates() as $runtimeId => $tag) {
            $name = $tag->getString("name");
            if (!$this->existsId($name)) continue;

            $id = $this->getId($name);
            if ($id < 470 && array_key_exists($runtimeId, $runtimeToLegacyMap)) {
                $legacy = $mapping->fromRuntimeId($runtimeId);
                $damage = $legacy ^ ($id << Block::INTERNAL_METADATA_BITS);
                if (array_key_exists($damage, $remap) && in_array($id, $remapIds, true)) {
                    $damage = $remap[$damage];
                }
            } else {
                if ($id === $idCheck) {
                    if (($id === 472 || str_contains($name, "button")) && $damage == 5) {
                        $damage = 8;
                    } else {
                        $damage++;
                    }
                } else {
                    $damage = 0;
                    $idCheck = $id;
                }
            }
            //echo $runtimeId . " : " . $id . " : " . $damage . " : " . ($id < 470 && array_key_exists($runtimeId, $runtimeToLegacyMap) ? "true" : "false") . "\n";
            $states = $tag->getCompoundTag("states");
            //if ($id === 251 || $id === 222) echo $states->toString() . "\n";
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