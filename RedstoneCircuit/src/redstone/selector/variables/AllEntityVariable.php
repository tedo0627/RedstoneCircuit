<?php

namespace redstone\selector\variables;

use pocketmine\Server;
use pocketmine\command\CommandSender;

use function array_merge;

class AllEntityVariable implements IVariable {

    public function getVariable() : string {
        return "e";
    }

    public function getEntities(CommandSender $sender, string $args, array $arguments) : array {
        $array = [];
        foreach (Server::getInstance()->getLevels() as $level) {
            $array = array_merge($array, $level->getEntities());
        }
        return $array;
    }
}