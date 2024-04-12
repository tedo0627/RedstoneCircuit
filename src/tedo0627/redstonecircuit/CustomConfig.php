<?php

namespace tedo0627\redstonecircuit;

use pocketmine\utils\Config;

class CustomConfig {

    private static bool $callEvent = false;
    private static bool $fixScheduleUpdate = false;

    public static function load(Config $config): void {
        self::$callEvent = $config->get("event", false);
        self::$fixScheduleUpdate = $config->get("fixScheduleUpdate", false);
    }

    public static function isCallEvent(): bool {
        return self::$callEvent;
    }

    public static function isFixScheduleUpdate(): bool {
        return self::$fixScheduleUpdate;
    }
}