<?php

namespace redstone\blockEntities;

use pocketmine\block\Block;

use pocketmine\entity\Entity;

use pocketmine\inventory\InventoryHolder;

use pocketmine\item\Item;
use pocketmine\item\ProjectileItem;

use pocketmine\math\Vector3;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

use pocketmine\tile\Container;
use pocketmine\tile\ContainerTrait;
use pocketmine\tile\Nameable;
use pocketmine\tile\NameableTrait;
use pocketmine\tile\Tile;
use pocketmine\tile\Spawnable;


use redstone\inventories\DispenserInventory;

class BlockEntityDispenser extends Spawnable implements InventoryHolder, Container, Nameable {
    use NameableTrait {
        addAdditionalSpawnData as addNameSpawnData;
    }
    use ContainerTrait;

    protected $inventory;

    protected function readSaveData(CompoundTag $nbt) : void {
        $this->loadName($nbt);

        $this->inventory = new DispenserInventory($this);
        $this->loadItems($nbt);
    }

    protected function writeSaveData(CompoundTag $nbt) : void {
        $this->saveName($nbt);
        $this->saveItems($nbt);
    }

    public function getName() : string{
        return "Dispenser";
    }
        
    public function getDefaultName() : string{
        return "Dispenser";
    }
        
    public function getInventory() {
        return $this->inventory;
    }
        
    public function getRealInventory() {
        return $this->inventory;
    }

    public function dropItem() : void {
        $inventory = $this->getInventory();
        $random = [];
        for ($i = 0; $i < 9; ++$i) {
            $item = $inventory->getItem($i);
            if ($item->getId() != 0) {
                $random[] = $i;
            }
        }

        if (count($random) == 0) {
            $this->level->broadcastLevelEvent($this->add(0.5, 0.5, 0.5), LevelEventPacket::EVENT_SOUND_CLICK_FAIL, 1200);
            return;
        }

        $slot = $random[mt_rand(0, count($random) - 1)];
        $item = $inventory->getItem($slot);
        $drop = clone($item);
        $drop->setCount(1);

        $block = $this->getBlock();
        $damage = $block->getDamage();
        if ($damage >= 8) {
            $damage -= 8;
        }

        $item->setCount($item->getCount() - 1);
        if ($item->getCount() <= 0) {
            $item = Item::get(0);
        }
        $inventory->setItem($slot, $item);

        $motion = new Vector3();
        $motion = $motion->getSide($damage);
        $pos = new Vector3($this->x + $motion->x * 2 + 0.5, $this->y + ($motion->y < 0 ? $motion->y : 0.5), $this->z + $motion->z * 2 + 0.5);

        /*
        if ($drop instanceof ProjectileItem) {
            $nbt = Entity::createBaseNBT($this, $motion, 0, 0);

            $projectile = Entity::createEntity($drop->getProjectileEntityType(), $this->getLevel(), $nbt);
            if($projectile !== null){
                $projectile->setMotion($projectile->getMotion()->multiply($drop->getThrowForce()));
            }
            $projectile->spawnToAll();
            $this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_THROW, 0);
            return;
        }
        */

        if ($drop->getId() == 325) {
            $side = $block->getSide($damage);
            $sideId = $side->getId();
            $dropDamage = $drop->getDamage();
            if ($dropDamage == 0 && ($sideId == 8 || $sideId == 10)) {
                $this->level->setBlock($side, Block::get(0));
                $drop->setDamage($sideId);
                if ($inventory->canAddItem($drop)) {
                    $inventory->addItem($drop);
                    $this->level->broadcastLevelEvent($this->add(0.5, 0.5, 0.5), LevelEventPacket::EVENT_SOUND_CLICK, 1000);
                    return;
                }
            } elseif (($dropDamage == 8 || $dropDamage == 10) && $side->getId() == 0) {
                $this->level->setBlock($side, Block::get($dropDamage));
                $drop->setDamage(0);
                if ($inventory->canAddItem($drop)) {
                    $inventory->addItem($drop);
                    $this->level->broadcastLevelEvent($this->add(0.5, 0.5, 0.5), LevelEventPacket::EVENT_SOUND_CLICK, 1000);
                    return;
                }
            }
        }

        $this->level->dropItem($pos, $drop, $motion->multiply(0.3));
        $this->level->broadcastLevelEvent($this->add(0.5, 0.5, 0.5), LevelEventPacket::EVENT_SOUND_CLICK, 1000);
    }
}