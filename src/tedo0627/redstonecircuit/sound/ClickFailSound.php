<?php

namespace tedo0627\redstonecircuit\sound;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelEvent;
use pocketmine\world\sound\Sound;

class ClickFailSound implements Sound {

    private float $pitch;

    public function __construct(float $pitch = 0){
        $this->pitch = $pitch;
    }

    public function getPitch() : float{
        return $this->pitch;
    }

    public function encode(Vector3 $pos) : array{
        return [LevelEventPacket::create(LevelEvent::SOUND_CLICK_FAIL, (int) ($this->pitch * 1000), $pos)];
    }
}