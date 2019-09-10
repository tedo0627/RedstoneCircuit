<?php

namespace redstone\selector\variables;

use pocketmine\Server;

use pocketmine\command\CommandSender;

class AllPlayerVariable implements IVariable {

    public function getVariable() : string {
        return "a";
    }

    public function getEntities(CommandSender $sender, string $args, array $arguments) : array {
        return Server::getInstance()->getOnlinePlayers();
    }
}