<?php

namespace tedo0627\redstonecircuit\block;

use pocketmine\math\Vector3;
use pocketmine\world\Position;
use pocketmine\world\World;
use ReflectionClass;
use SplPriorityQueue;

class ScheduleUpdateHelper {

    /**
     * Replace block update delays
     *
     * SplPriorityQueue is used and cannot simply be rewritten in reflection.
     * Therefore, all queue values are retrieved and reinserted once, which is inefficient.
     *
     * @see World::scheduleDelayedBlockUpdate()
     * @see World::scheduledBlockUpdateQueueIndex
     * @see World::scheduledBlockUpdateQueue
     */
    public static function scheduleUpdate(Position $pos, int $delay): void {
        $world = $pos->getWorld();
        $reflection = new ReflectionClass(get_class($world));

        $property = $reflection->getProperty("scheduledBlockUpdateQueue");
        /** @var SplPriorityQueue $value */
        $value = $property->getValue($world);

        $vec = new Vector3((int) $pos->x, (int) $pos->y, (int) $pos->z);
        $fns = [];
        $override = false;
        while ($value->count() > 0) {
            /** @var array $array */
            $array = $value->extract();
            $updatePosition = $array["data"];
            $priority = $array["priority"];

            if ($vec->equals($updatePosition)) {
                $priority = $delay + $world->getServer()->getTick();
                $override = true;
            }

            $fn = function () use ($value, $updatePosition, $priority) {
                $value->insert($updatePosition, $priority);
            };
            $fns[] = $fn;
        }

        foreach ($fns as $fn) {
            $fn();
        }

        if (!$override) {
            $world->scheduleDelayedBlockUpdate($pos, $delay);
        } else {
            $property->setValue($world, $value);

            $property = $reflection->getProperty("scheduledBlockUpdateQueueIndex");
            $value = $property->getValue($world);
            $index = World::blockHash($pos->x, $pos->y, $pos->z);
            $value[$index] = $delay;
            $property->setValue($world, $value);
        }
    }
}