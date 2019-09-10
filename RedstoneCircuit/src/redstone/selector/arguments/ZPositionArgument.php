<?php

namespace redstone\selector\arguments;

use pocketmine\Player;

use pocketmine\command\CommandSender;

class ZPositionArgument extends BaseArgument {
    
    public function getArgument() : string {
        return "z";
    }

    public function selectgetEntities(CommandSender $sender, string $argument, array $arguments, array $entities) : array {
        $array = [];
        $value = $this->getValue($argument);
        $pos = floatval($value);

        $dz = null;
        foreach ($arguments as $arg) {
            if ($arg->getArgument() == "dz") {
                $dz = floatval($arg->getValue($argument));
            }
        }

        foreach ($entities as $entity) {
            $z = $entity->getZ();

            if ($dz != null) {
                if ($dz >= 0 && $pos <= $z && $z <= $pos + $dz) {
                    $array[] = $entity;
                }
                if ($dz < 0 && $pos + $dz <= $z && $z <= $pos) {
                    $array[] = $entity;
                }
                continue;
            }

            if ($z == $pos) {
                $array[] = $entity;
            }
        }
        
        return $array;
    }
}