<?php

namespace tedo0627\redstonecircuit\sound;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\world\sound\Sound;

class DetachSound implements Sound {

    public function encode(Vector3 $pos) : array{
        return [LevelSoundEventPacket::nonActorSound(LevelSoundEvent::DETACH, $pos, false)];
    }
}