<?php

namespace redstone\utils;

use pocketmine\utils\Config;


use redstone\Main;

class CustomConfig {

    private $config;

    public function __construct() {
        $main = Main::getInstance();
        $main->saveDefaultConfig();
        $main->reloadConfig();
        $this->config = new Config($main->getDataFolder() . "config.yml", Config::YAML);
    }

    public function isSaveScheduledBlockUpdate() : bool {
        return $this->config->get("isSaveScheduledBlockUpdate", true);
    }

    public function isCommandBlockEnabled() : bool {
        return $this->config->get("enable-command-block", true);
    }

    public function getMaxPistonPushBlocks() : int {
        return $this->config->get("max-piston-push-blocks", 12);
    }
}