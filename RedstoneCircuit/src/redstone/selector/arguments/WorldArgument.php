<?php

namespace redstone\selector\arguments;

use pocketmine\command\CommandSender;

class WorldArgument extends BaseArgument {
    
    public function getArgument() : string {
        return "w";
    }

    public function selectgetEntities(CommandSender $sender, string $argument, array $arguments, array $entities) : array {
        $array = [];
        $world = $this->getValue($argument);
        $exclud = $this->isExcluded($argument);

        foreach ($entities as $entity) {
            $name = $entity->getLevel()->getName();
            if ($name == $world && !$exclud) {
                $array[] = $entity;
                continue;
            }

            if ($name != $world && $exclud) {
                $array[] = $entity;
            }
        }
        
        return $array;
    }
}