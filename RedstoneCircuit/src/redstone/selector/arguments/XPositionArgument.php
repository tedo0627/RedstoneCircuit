<?php

namespace redstone\selector\arguments;

use pocketmine\command\CommandSender;

use function floatval;

class XPositionArgument extends BaseArgument {
    
    public function getArgument() : string {
        return "x";
    }

    public function selectgetEntities(CommandSender $sender, string $argument, array $arguments, array $entities) : array {
        $array = [];
        $value = $this->getValue($argument);
        $pos = floatval($value);

        $dx = null;
        foreach ($arguments as $arg) {
            if ($arg->getArgument() == "dx") {
                $dx = floatval($arg->getValue($argument));
            }
        }

        foreach ($entities as $entity) {
            $x = $entity->getX();

            if ($dx != null) {
                if ($dx >= 0 && $pos <= $x && $x <= $pos + $dx) {
                    $array[] = $entity;
                }
                if ($dx < 0 && $pos + $dx <= $x && $x <= $pos) {
                    $array[] = $entity;
                }
                continue;
            }

            if ($x == $pos) {
                $array[] = $entity;
            }
        }
        
        return $array;
    }
}