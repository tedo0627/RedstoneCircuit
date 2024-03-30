<?php

namespace tedo0627\redstonecircuit\registry;

use Closure;
use pocketmine\block\Block;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\block\convert\BlockObjectToStateSerializer;
use pocketmine\data\bedrock\block\convert\BlockStateToObjectDeserializer;
use pocketmine\data\bedrock\item\ItemDeserializer;
use pocketmine\data\bedrock\item\ItemSerializer;
use pocketmine\data\bedrock\item\ItemSerializerDeserializerRegistrar;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\item\StringToItemParser;
use pocketmine\utils\StringToTParser;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use ReflectionClass;
use ReflectionMethod;

class BlockRegistryHelper {

    /**
     * @see RuntimeBlockStateRegistry::register() Call this method error avoiding.
     * @see RuntimeBlockStateRegistry::$typeIndex
     * @see RuntimeBlockStateRegistry::fillStaticArrays()
     */
    public static function runtime(Block $block): void {
        $registry = RuntimeBlockStateRegistry::getInstance();
        self::reflectionCallback(null, "typeIndex", $registry,
            function (array $value) use ($block) {
                $value[$block->getTypeId()] = $block;
                return $value;
            }
        );
        $method = self::getMethod(RuntimeBlockStateRegistry::class, "fillStaticArrays");
        foreach ($block->generateStatePermutations() as $v) {
            $method->invoke($registry, $v->getStateId(), $v);
        }
    }

    /**
     * @see BlockObjectToStateSerializer::map() Call this method error avoiding.
     * @see BlockObjectToStateSerializer::$serializers
     */
    public static function serialize(Block $block, Closure $closure): void {
        $serializer = GlobalBlockStateHandlers::getSerializer();
        self::reflectionCallback(null, "serializers", $serializer,
            function (array $value) use ($block, $closure) {
                $value[$block->getTypeId()] = $closure;
                return $value;
            }
        );
    }

    /**
     * @see BlockStateToObjectDeserializer::map Call this method error avoiding.
     * @see BlockStateToObjectDeserializer::$deserializeFuncs
     */
    public static function deserialize(string $id, Closure $closure): void {
        $deserializer = GlobalBlockStateHandlers::getDeserializer();
        self::reflectionCallback(null, "deserializeFuncs", $deserializer,
            function (array $value) use ($id, $closure) {
                $value[$id] = $closure;
                return $value;
            }
        );
    }

    /**
     * @see StringToItemParser::registerBlock() Call this method error avoiding.
     * @see StringToItemParser::register()
     *
     * @see StringToItemParser::reprocess()
     * @see StringToItemParser::$callbackMap
     *
     * @see StringToItemParser::$reverseMap
     */
    public static function stringToItemParser(string|array $alias, Block $block): void {
        $callback = fn() => clone $block;
        $callback = fn(string $input) => $callback($input)->asItem();

        $parser = StringToItemParser::getInstance();

        if (is_string($alias)) {
            $alias = [$alias];
        }

        $method = self::getMethod(StringToItemParser::class, "reprocess");
        foreach ($alias as $name) {
            $key = $method->invoke($parser, $name);

            self::reflectionCallback(StringToTParser::class, "callbackMap", $parser,
                function (array $value) use ($key, $callback) {
                    $value[$key] = $callback;
                    return $value;
                }
            );

            $item = $callback($name);

            self::reflectionCallback(null, "reverseMap", $parser,
                function (array $value) use ($item, $name, $callback) {
                    $value[$item->getStateId()][$name] = $callback;
                    return $value;
                }
            );
        }
    }

    /**
     * @see VanillaBlocks::register() Call this method error avoiding.
     * @see VanillaBlocks::$members
     */
    public static function vanillaBlocks(string $name, Block $block): void {
        $upperName = mb_strtoupper($name);
        self::reflectionCallback(VanillaBlocks::class, "members", null,
            function (array $value) use ($upperName, $block) {
                $value[$upperName] = $block;
                return $value;
            }
        );
    }

    /**
     * @see ItemSerializerDeserializerRegistrar::map1to1Block() Call this method error avoiding.
     *
     * @see ItemSerializer::mapBlock()
     * @see ItemSerializer::$blockItemSerializers
     *
     * @see ItemDeserializer::mapBlock()
     * @see ItemDeserializer::$deserializers
     */
    public static function item(string $id, Block $block): void {
        self::reflectionCallback(null, "blockItemSerializers", GlobalItemDataHandlers::getSerializer(),
            function (array $value) use ($block, $id) {
                $index = $block->getTypeId();
                $value[$index] = fn() => new SavedItemData($id);
                return $value;
            }
        );

        self::reflectionCallback(null, "deserializers", GlobalItemDataHandlers::getDeserializer(),
            function (array $value) use ($block, $id) {
                $closure = fn() => $block;
                $closure = fn(SavedItemData $data) => $closure($data)->asItem();
                $value[$id] = $closure;
                return $value;
            }
        );
    }

    /**
     * Change properties using reflection.
     *
     * @param ?string $className Name of class to be changed in reflection.
     * If null, get class from $object.
     * @param string $propertyName Name of property to be changed in reflection.
     * @param mixed $object Objects to be changed in reflection.
     * @param Closure $closure Callback that edits the value retrieved in reflection and returns it.
     * The edited value must be returned.
     */
    private static function reflectionCallback(?string $className, string $propertyName, mixed $object, Closure $closure): void {
        if ($className === null) {
            $className = get_class($object);
        }

        $reflection = new ReflectionClass($className);
        $property = $reflection->getProperty($propertyName);
        $value = $property->getValue($object);
        $value = $closure($value);
        $property->setValue($object, $value);
    }

    private static function getMethod(string $className, string $methodName): ReflectionMethod {
        return (new ReflectionClass($className))->getMethod($methodName);
    }
}