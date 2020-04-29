<?php

namespace redstone\blocks;

use pocketmine\Player;

use pocketmine\block\Block;
use pocketmine\block\Solid;

use pocketmine\item\Item;

use pocketmine\math\Vector3;

use pocketmine\network\mcpe\protocol\types\ContainerIds;

use pocketmine\tile\Tile;

use redstone\blockEntities\BlockEntityCommandBlock;

class BlockCommand extends Solid implements IRedstone {
    use RedstoneTrait;

    protected $id = self::COMMAND_BLOCK;

    public function __construct(int $meta = 0){
        $this->meta = $meta;
    }

    public function getName() : string {
        return "Command Block";
    }

    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool {
        if($player !== null) {
            if (!$player->isOp() || !$player->isCreative()) {
                return false;
            }

            $faces = [4, 2, 5, 3];
            $damage = $faces[$player->getDirection()];
            if ($player->getPitch() > 45) {
                $damage = 1;
            } else if ($player->getPitch() < -45) {
                $damage = 0;
            }
        }

        $this->setDamage($damage);
        $this->level->setBlock($this, $this, true, true);
        
        Tile::createTile("BlockEntityCommandBlock", $this->getLevel(), BlockEntityCommandBlock::createNBT($this->asVector3()));
        return true;
    }

    public function onActivate(Item $item, Player $player = null) : bool {
        if($player instanceof Player){
            if (!$player->isOp() || !$player->isCreative()) {
                return true;
            }

            $command = $this->getBlockEntity();
            $inventory = $command->getInventory();
            if ($player->getWindowId($inventory) != ContainerIds::NONE) {
                $inventory->open($player);
            } else {
                $player->addWindow($inventory);
            }
        }
        return true;
    }

    public function getDrops(Item $item) : array {
        return [];
    }
    
    public function getBlockEntity() : BlockEntityCommandBlock {
        $tile = $this->getLevel()->getTile($this);
        $command = null;
        if($tile instanceof BlockEntityCommandBlock){
            $command = $tile;
        }else{
            $command = Tile::createTile("BlockEntityCommandBlock", $this->getLevel(), BlockEntityCommandBlock::createNBT($this));
        }
        return $command;
    }
    
    public function getMode() : int {
        return 0;
    }
    
    public function getStrongPower(int $face) : int {
        return 0;
    }

    public function getWeakPower(int $face) : int {
        return 0;
    }

    public function isPowerSource() : bool {
        return false;
    }

    public function onRedstoneUpdate() : void {
        $tile = $this->getBlockEntity();
        $power = $this->isBlockPowered($this->asVector3());
        if ($power && !$tile->isPowered()) {
            $tile->setPowered(true);

            if ($this->getMode() == 0) {
                $tile->dispatch();
            }

        } elseif (!$power) {
            $tile->setPowered(false);
        }
    }
}