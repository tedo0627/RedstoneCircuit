<?php

namespace redstone\selector\arguments;

use pocketmine\Player;

use pocketmine\command\CommandSender;

use function count;
use function ctype_digit;
use function explode;
use function floatval;
use function intval;

class LevelArgument extends BaseArgument {
    
    public function getArgument() : string {
        return "l";
    }

    public function selectgetEntities(CommandSender $sender, string $argument, array $arguments, array $entities) : array {
        $array = [];
        $value = $this->getValue($argument);
        if (ctype_digit($value)) {
            $level = intval($value);
            foreach ($entities as $entity) {
                if (!($entity instanceof Player)) {
                    continue;
                }
    
                if ($entity->getXpLevel() == $level) {
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
            $level = intval($split[1]);
            foreach ($entities as $entity) {
                if (!($entity instanceof Player)) {
                    continue;
                }

                if ($entity->getXpLevel() <= $level) {
                    $array[] = $entity;
                }
            }

            return $array;
        }

        if ($split[1] == "") {
            $level = intval($split[0]);
            foreach ($entities as $entity) {
                if (!($entity instanceof Player)) {
                    continue;
                }

                if ($entity->getXpLevel() >= $level) {
                    $array[] = $entity;
                }
            }
            
            return $array;
        }

        $min = floatval($split[0]);
        $max = floatval($split[1]);

        foreach ($entities as $entity) {
            if (!($entity instanceof Player)) {
                continue;
            }

            $level = $entity->getXpLevel();
            if ($min <= $level && $level <= $max) {
                $array[] = $entity;
            }
        }
        
        return $array;
    }
}