<?php

namespace redstone\selector\variables;

use pocketmine\command\CommandSender;

interface IVariable {
    
    public function getVariable() : string;

    public function getEntities(CommandSender $sender, string $args, array $arguments) : array;
}