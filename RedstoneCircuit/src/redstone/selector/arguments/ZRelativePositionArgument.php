<?php

namespace redstone\selector\arguments;

use pocketmine\command\CommandSender;

class ZRelativePositionArgument extends BaseArgument {

    public function getArgument() : string {
        return "dz";
    }

    public function selectgetEntities(CommandSender $sender, string $argument, array $arguments, array $entities) : array {
        return $entities;
    }
}