<?php

namespace redstone\selector\arguments;

use pocketmine\Player;

use pocketmine\command\CommandSender;

class NameArgument extends BaseArgument {
    
    public function getArgument() : string {
        return "name";
    }

    public function selectgetEntities(CommandSender $sender, string $argument, array $arguments, array $entities) : array {
        $array = [];
        $name = $this->getValue($argument);
        $exclud = $this->isExcluded($argument);

        foreach ($entities as $entity) {
            if (!($entity instanceof Player)) {
                continue;
            }

            $n = $entity->getName();
            if ($n == $name && !$exclud) {
                $array[] = $entity;
                continue;
            }

            if ($n != $name && $exclud) {
                $array[] = $entity;
            }
        }
        
        return $array;
    }
}