<?php

namespace redstone\selector\arguments;

use pocketmine\command\CommandSender;

use function count;
use function ctype_digit;
use function explode;
use function floatval;

class XRotationArgument extends BaseArgument {
    
    public function getArgument() : string {
        return "rx";
    }

    public function selectgetEntities(CommandSender $sender, string $argument, array $arguments, array $entities) : array {
        $array = [];
        $value = $this->getValue($argument);
        if (ctype_digit($value)) {
            $x = floatval($value);
            foreach ($entities as $entity) {
                if ($entity->getPitch() == $x) {
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
            $x = floatval($split[1]);
            foreach ($entities as $entity) {
                if ($entity->getPitch() <= $x) {
                    $array[] = $entity;
                }
            }
            return $array;
        }

        if ($split[1] == "") {
            $x = floatval($split[0]);
            foreach ($entities as $entity) {
                if ($entity->getPitch() >= $x) {
                    $array[] = $entity;
                }
            }
            return $array;
        }

        $min = floatval($split[0]);
        $max = floatval($split[1]);

        foreach ($entities as $entity) {
            $pitch = $entity->getPitch();
            if ($min <= $pitch && $pitch <= $max) {
                $array[] = $entity;
            }
        }
        return $array;
    }
}