<?php

namespace redstone\utils;

use pocketmine\Server;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;

use redstone\Main;
use redstone\listeners\ScheduledBlockUpdateListener;

use ReflectionClass;
use function count;
use function explode;
use function get_class;

class ScheduledBlockUpdateLoader {

    private $isActivate;

    private $config;

    public function __construct() {
        $main = Main::getInstance();

        $this->isActivate = $main->getCustomConfig()->isSaveScheduledBlockUpdate();
        if (!$this->isActivate) {
            return;
        }

        $main->getServer()->getPluginManager()->registerEvents(new ScheduledBlockUpdateListener(), $main);
        $this->config = new Config($main->getDataFolder() . "ScheduledBlockUpdate.yml", Config::YAML);
    }

    public function isActivate() : bool {
        return $this->isActivate;
    }

    public function loadLevel(Level $level) : void {
        if (!$this->config->exists($level->getName())) {
            return;
        }
        $levelDatas = $this->config->get($level->getName());
        for ($i = 0; $i < count($levelDatas); ++$i) {
            $sp = explode(":", $levelDatas[$i]);
            $position = new Vector3((int) $sp[0], (int) $sp[1], (int) $sp[2]);
            if (!$level->isChunkLoaded($position->getX() >> 4, $position->getZ() >> 4)) {
                $level->loadChunk($position->getX() >> 4, $position->getZ() >> 4);
            }
            $level->scheduleDelayedBlockUpdate($position, (int) $sp[3]);
        }
    }

    public function saveLevel(Level $level) {
        $levelDatas = [];
        $tick = Server::getInstance()->getTick();

        $reflection = new ReflectionClass(get_class($level));
        $property = $reflection->getProperty('scheduledBlockUpdateQueue');
        $property->setAccessible(true);
        $queue = $property->getValue($level);

		while($queue->count() > 0){
            $value = $queue->extract();
            $pos = $value["data"];
            $delay = $value["priority"] - $tick;
            $levelDatas[] = $pos->getX() . ":" . $pos->getY() . ":" . $pos->getZ() . ":" . $delay;
        }

        $this->config->set($level->getName(), $levelDatas);
        $this->config->save();
    }
}