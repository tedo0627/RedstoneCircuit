<?php

namespace redstone\selector\arguments;

use pocketmine\command\CommandSender;

class YRelativePositionArgument extends BaseArgument {

    public function getArgument() : string {
        return "dy";
    }

    public function selectgetEntities(CommandSender $sender, string $argument, array $arguments, array $entities) : array {
        return $entities;
    }
}