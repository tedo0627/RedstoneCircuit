<?php

namespace redstone\selector\arguments;

use pocketmine\command\CommandSender;

interface IArgument {

    public function getArgument() : string;

    public function getValue(string $argument) : string;

    public function isExcluded(string $argument) : bool;

    public function selectgetEntities(CommandSender $sender, string $argument, array $arguments, array $entities) : array;
}