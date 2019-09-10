<?php

namespace redstone\selector\arguments;

use pocketmine\command\CommandSender;

class XRelativePositionArgument extends BaseArgument {

    public function getArgument() : string {
        return "dx";
    }

    public function selectgetEntities(CommandSender $sender, string $argument, array $arguments, array $entities) : array {
        return $entities;
    }
}