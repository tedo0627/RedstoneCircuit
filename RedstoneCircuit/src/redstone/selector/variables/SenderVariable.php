<?php

namespace redstone\selector\variables;

use pocketmine\entity\Entity;
use pocketmine\command\CommandSender;

class SenderVariable implements IVariable {

    public function getVariable() : string {
        return "s";
    }

    public function getEntities(CommandSender $sender, string $args, array $arguments) : array {
        if ($sender instanceof Entity) {
            return [$sender];
        }
        return [];
    }
}