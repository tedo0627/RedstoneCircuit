<?php

namespace redstone\selector\arguments;

use pocketmine\command\CommandSender;

use pocketmine\level\Position;

use function count;
use function ctype_digit;
use function explode;
use function floatval;

class DistanceArgument extends BaseArgument {
    
    public function getArgument() : string {
        return "r";
    }

    public function selectgetEntities(CommandSender $sender, string $argument, array $arguments, array $entities) : array {
        if (!($sender instanceof Position)) {
            return [];
        }

        $array = [];
        $value = $this->getValue($argument);
        if (ctype_digit($value)) {
            $rage = floatval($value);
            foreach ($entities as $entity) {
                if ($entity->distance($sender) == $rage) {
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
            $rage = floatval($split[1]);
            foreach ($entities as $entity) {
                if ($entity->distance($sender) <= $rage) {
                    $array[] = $entity;
                }
            }
            return $array;
        }

        if ($split[1] == "") {
            $rage = floatval($split[0]);
            foreach ($entities as $entity) {
                if ($entity->distance($sender) >= $rage) {
                    $array[] = $entity;
                }
            }
            return $array;
        }

        $min = floatval($split[0]);
        $max = floatval($split[1]);

        foreach ($entities as $entity) {
            $distance = $entity->distance($sender);
            if ($min <= $distance && $distance <= $max) {
                $array[] = $entity;
            }
        }
        return $array;
    }
}