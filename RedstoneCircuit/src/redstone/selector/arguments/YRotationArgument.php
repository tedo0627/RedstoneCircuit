<?php

namespace redstone\selector\arguments;

use pocketmine\command\CommandSender;

use function count;
use function ctype_digit;
use function explode;
use function floatval;

class YRotationArgument extends BaseArgument {
    
    public function getArgument() : string {
        return "ry";
    }

    public function selectgetEntities(CommandSender $sender, string $argument, array $arguments, array $entities) : array {
        $array = [];
        $value = $this->getValue($argument);
        if (ctype_digit($value)) {
            $y = floatval($value);
            foreach ($entities as $entity) {
                if ($entity->getYaw() == $y) {
                    $array[] = $entity;
                }
            }
            return $array;
        }

        $split = explode("..", $value);
        if (count($split) < 2) {
            return [];
        }

        if ($split[0] == "") {
            $y = floatval($split[1]);
            foreach ($entities as $entity) {
                if ($entity->getYaw() <= $y) {
                    $array[] = $entity;
                }
            }
            return $array;
        }

        if ($split[1] == "") {
            $y = floatval($split[0]);
            foreach ($entities as $entity) {
                if ($entity->getYaw() >= $y) {
                    $array[] = $entity;
                }
            }
            return $array;
        }

        $min = floatval($split[0]);
        $max = floatval($split[1]);

        foreach ($entities as $entity) {
            $yaw = $entity->getYaw();
            if ($min <= $yaw && $yaw <= $max) {
                $array[] = $entity;
            }
        }
        return $array;
    }
}