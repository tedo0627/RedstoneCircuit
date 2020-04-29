<?php

namespace redstone\selector\variables;

use pocketmine\Server;
use pocketmine\command\CommandSender;

use function shuffle;

class RandomPlayerVariable implements IVariable {

    public function getVariable() : string {
        return "r";
    }

    public function getEntities(CommandSender $sender, string $args, array $arguments) : array {
        $players = Server::getInstance()->getOnlinePlayers();
        shuffle($players);
        return $players;
    }
}