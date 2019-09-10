<?php

namespace redstone\selector\arguments;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\command\CommandSender;

class GamemodeArgument extends BaseArgument {
    
    public function getArgument() : string {
        return "m";
    }

    public function selectgetEntities(CommandSender $sender, string $argument, array $arguments, array $entities) : array {
        $array = [];
        $type = $this->getValue($argument);
        $exclud = $this->isExcluded($argument);
        $gamemode = Server::getGamemodeFromString($type);

        foreach ($entities as $entity) {
            if (!($entity instanceof Player)) {
                continue;
            }

            $gm = $entity->getGamemode();
            if ($gm == $gamemode && !$exclud) {
                $array[] = $entity;
                continue;
            }

            if ($gm != $gamemode && $exclud) {
                $array[] = $entity;
            }
        }
        
        return $array;
    }
}