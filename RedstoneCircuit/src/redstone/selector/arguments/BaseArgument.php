<?php

namespace redstone\selector\arguments;

use pocketmine\command\CommandSender;

use function count;
use function explode;
use function preg_replace;
use function substr;

abstract class BaseArgument implements IArgument {

    public abstract function getArgument() : string;

    public function getValue(string $argument) : string {
        $key = $this->getArgument();

        $str = strstr($argument, "[");
        $str = substr($str, 1, -1);
        $str = preg_replace("/ /", "", $str);
        $split = explode(",", $str);
        foreach ($split as $s) {
            $pair = explode("=", $s);
            if (count($pair) < 2) {
                continue;
            }

            $k = $pair[0];
            if (substr($k, -1) == "!") {
                $k = substr($k, 0, -1);
            }

            if ($k == $key) {
                return $pair[1];
            }
        }

        return "";
    }

    public function isExcluded(string $argument) : bool {
        $key = $this->getArgument();

        $str = strstr($argument, "[");
        $str = substr($str, 1, -1);
        $str = preg_replace("/ /", "", $str);
        $split = explode(",", $str);
        foreach ($split as $s) {
            $pair = explode("=", $s);
            if (count($pair) < 2) {
                continue;
            }

            if ($pair[0] == $key) {
                continue;
            }

            return substr($pair[0], -1) == "!";
        }

        return false;
    }

    public abstract function selectgetEntities(CommandSender $sender, string $argument, array $arguments, array $entities) : array;
}