<?php

namespace redstone\blocks;

use pocketmine\block\Solid;

class BlockSlime extends Solid {
    
    protected $id = self::SLIME;
    
    public function __construct(int $meta = 0){
        $this->meta = $meta;
    }

	public function getName() : string{
		return "Slime";
	}

	public function getHardness() : float{
		return 0;
	}
}