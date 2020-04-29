<?php

namespace redstone\selector\arguments;

use pocketmine\entity\EntityIds;
use pocketmine\Player;

use pocketmine\command\CommandSender;

use function ctype_digit;
use function intval;

class EntityTypeArgument extends BaseArgument {
    
    public function getArgument() : string {
        return "type";
    }

    public function selectgetEntities(CommandSender $sender, string $argument, array $arguments, array $entities) : array {
        $array = [];
        $value = $this->getValue($argument);
        $exclud = $this->isExcluded($argument);
        if (!ctype_digit($value)) {
            return [];
        }
        $type = intval($value);

        foreach ($entities as $entity) {
            $id = $entity::NETWORK_ID;
            if ($entity instanceof Player) {
                $id = EntityIds::PLAYER;
            }

            if ($id == $type && !$exclud) {
                $array[] = $entity;
                continue;
            }
            if ($id != $type && $exclud) {
                $array[] = $entity;
            }
        }
        
        return $array;
    }
}