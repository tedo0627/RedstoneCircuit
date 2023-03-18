<?php

namespace tedo0627\redstonecircuit\block;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\GlobalItemTypeDictionary;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\protocol\serializer\NetworkNbtSerializer;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializerContext;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Utils;
use Webmozart\PathUtil\Path;
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
        $dir = dirname(__DIR__);
        if (!str_ends_with($dir, ".phar")) {
            $remove = "src" . DIRECTORY_SEPARATOR . "tedo0627" . DIRECTORY_SEPARATOR . "redstonecircuit";
            $dir = mb_substr($dir, 0, - (strlen($remove)));
        }
        $dir = $dir . "resources" . DIRECTORY_SEPARATOR ."block_id_map.json";
        $json = json_decode(file_get_contents($dir), true);
        foreach ($json as $name => $id) {
            // pistonArmCollision -> piston_arm_collision
            if (str_contains(strtolower($name), "piston")) {
                $name = ltrim(strtolower(preg_replace('/[A-Z]/', '_\0', $name)), '_');
            }
            $this->idToName[$id] = $name;
            $this->nameToId[$name] = $id;
        }

        $legacyStateMapReader = PacketSerializer::decoder(
            Utils::assumeNotFalse(file_get_contents(Path::join(BEDROCK_DATA_PATH, "r12_to_current_block_map.bin")), "Missing required resource file"),
            0,
            new PacketSerializerContext(GlobalItemTypeDictionary::getInstance()->getDictionary())
        );
        $nbtReader = new NetworkNbtSerializer();
        while(!$legacyStateMapReader->feof()){
            $name = $legacyStateMapReader->getString();
            // pistonArmCollision -> piston_arm_collision
            if (str_contains(strtolower($name), "piston")) {
                $name = ltrim(strtolower(preg_replace('/[A-Z]/', '_\0', $name)), '_');
            }

            $id = $this->getId($name);
            $damage = $legacyStateMapReader->getLShort();

            $offset = $legacyStateMapReader->getOffset();
            $tag = $nbtReader->read($legacyStateMapReader->getBuffer(), $offset)->mustGetCompoundTag();
            $legacyStateMapReader->setOffset($offset);

            $states = $tag->getCompoundTag("states");
            $strStates = ltrim($states->toString(), "TAG_Compound=");
            $this->idToDamageToState[$id][$damage] = $states;
            $this->idToStateToDamage[$id][$strStates] = $damage;
        }

        $mapping = RuntimeBlockMapping::getInstance();
        $idCheck = -1;
        $damage = 0;
        foreach ($mapping->getBedrockKnownStates() as $tag) {
            $name = $tag->getString("name");
            if (!$this->existsId($name)) continue;

            $id = $this->getId($name);
            $states = $tag->getCompoundTag("states");
            $strStates = ltrim($states->toString(), "TAG_Compound=");
            if (array_key_exists($id, $this->idToStateToDamage) && array_key_exists($strStates, $this->idToStateToDamage[$id])) continue;

            if ($id === $idCheck) {
                $damage = ($id === 472 || str_contains($name, "button")) && $damage == 5 ? 8 : $damage + 1;
            } else {
                $damage = 0;
                $idCheck = $id;
            }

            $this->idToDamageToState[$id][$damage] = $states;
            $this->idToStateToDamage[$id][$strStates] = $damage;
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
        $strStates = ltrim($states->toString(), "TAG_Compound=");
        return $this->idToStateToDamage[$id][$strStates];
    }
}