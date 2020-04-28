<?php

namespace redstone\selector\arguments;

use pocketmine\command\CommandSender;

use function floatval;

class YPositionArgument extends BaseArgument {
    
    public function getArgument() : string {
        return "y";
    }

    public function selectgetEntities(CommandSender $sender, string $argument, array $arguments, array $entities) : array {
        $array = [];
        $value = $this->getValue($argument);
        $pos = floatval($value);

        $dy = null;
        foreach ($arguments as $arg) {
            if ($arg->getArgument() == "dy") {
                $dy = floatval($arg->getValue($argument));
            }
        }

        foreach ($entities as $entity) {
            $y = $entity->getY();

            if ($dy != null) {
                if ($dy >= 0 && $pos <= $y && $y <= $pos + $dy) {
                    $array[] = $entity;
                }
                if ($dy < 0 && $pos + $dy <= $y && $y <= $pos) {
                    $array[] = $entity;
                }
                continue;
            }

            if ($y == $pos) {
                $array[] = $entity;
            }
        }
        
        return $array;
    }
}