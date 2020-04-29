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
        return $this->config->get("is-save-block-update", true);
    }


    public function isEnableRedstoneWire() : bool {
        return $this->config->get("enable-redstone-wire", true);
    }

    public function isEnableRedstoneRepeater() : bool {
        return $this->config->get("enable-redstone-repeater", true);
    }

    public function isEnableRedstoneComparator() : bool {
        return $this->config->get("enable-redstone-comparator", true);
    }

    public function isEnableRedstoneTorch() : bool {
        return $this->config->get("enable-redstone-torch", true);
    }


    public function isEnableRedstoneBlock() : bool {
        return $this->config->get("enable-redstone-block", true);
    }

    public function isEnableLever() : bool {
        return $this->config->get("enable-lever", true);
    }

    public function isEnableButton() : bool {
        return $this->config->get("enable-button", true);
    }

    public function isEnablePressurePlate() : bool {
        return $this->config->get("enable-pressure-plate", true);
    }

    public function isEnableDaylightDetector() : bool {
        return $this->config->get("enable-daylight-detector", true);
    }

    public function isEnableObserver() : bool {
        return $this->config->get("enable-observer", true);
    }

    public function isEnableTrappedChest() : bool {
        return $this->config->get("enable-trapped-chest", true);
    }

    public function isEnableTripwire() : bool {
        return $this->config->get("enable-tripwire", true);
    }


    public function isEnableRedstoneLamp() : bool {
        return $this->config->get("enable-redstone-lamp", true);
    }

    public function isEnableNoteBlock() : bool {
        return $this->config->get("enable-note-block", true);
    }

    public function isEnableDropper() : bool {
        return $this->config->get("enable-dropper", true);
    }

    public function isEnableDispenser() : bool {
        return $this->config->get("enable-dispenser", true);
    }

    public function isEnableHopper() : bool {
        return $this->config->get("enable-hopper", true);
    }

    public function isEnablePiston() : bool {
        return $this->config->get("enable-piston", true);
    }

    public function isEnableCommandBlock() : bool {
        return $this->config->get("enable-command-block", true);
    }

    public function isEnableTnt() : bool {
        return $this->config->get("enable-tnt", true);
    }

    public function isEnableDoor() : bool {
        return $this->config->get("enable-door", true);
    }

    public function isEnableTrapDoor() : bool {
        return $this->config->get("enable-trap-door", true);
    }

    public function isEnableFenceGate() : bool {
        return $this->config->get("enable-fence-gate", true);
    }


    public function isEnableSlimeBlock() : bool {
        return $this->config->get("enable-slime-block", true);
    }


    public function getMaxPistonPushBlocks() : int {
        return $this->config->get("max-piston-push-blocks", 12);
    }
}