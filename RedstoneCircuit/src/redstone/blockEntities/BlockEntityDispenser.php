<?php

namespace redstone\blockEntities;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\Crops;
use pocketmine\block\Sapling;

use pocketmine\entity\Entity;
use pocketmine\entity\Living;

use pocketmine\inventory\InventoryHolder;

use pocketmine\item\Item;
use pocketmine\item\Armor;
use pocketmine\item\ProjectileItem;

use pocketmine\math\Vector3;
use pocketmine\math\AxisAlignedBB;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

use pocketmine\tile\Container;
use pocketmine\tile\ContainerTrait;
use pocketmine\tile\Nameable;
use pocketmine\tile\NameableTrait;
use pocketmine\tile\Spawnable;

use pocketmine\utils\Random;

use redstone\inventories\DispenserInventory;

use function count;
use function mt_rand;

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

        $drop = clone $item;
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
        $pos = new Vector3($this->x + $motion->x + 0.5, $this->y + $motion->y + 0.5, $this->z + $motion->z + 0.5);
        if ($motion->y <= 0) {
            $motion->y = 0.2;
        }

        if ($drop instanceof ProjectileItem) {
            $nbt = Entity::createBaseNBT($pos, $motion, 0, 0);

            $method = new \ReflectionMethod(get_class($drop), 'addExtraTags');
            $method->setAccessible(true);
            $method->invoke($drop, $nbt);

            $projectile = Entity::createEntity($drop->getProjectileEntityType(), $this->getLevel(), $nbt);
            if($projectile !== null){
                $projectile->setMotion($projectile->getMotion()->multiply($drop->getThrowForce()));
            }
            $projectile->spawnToAll();
            $this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_THROW, 0);
            $this->level->broadcastLevelEvent($this->add(0.5, 0.5, 0.5), LevelEventPacket::EVENT_SOUND_CLICK, 1000);
            return;
        }

        if ($drop instanceof Armor) {
            $p = $this->getSide($damage);
            $area = new AxisAlignedBB($p->x, $p->y, $p->z, $p->x + 1, $p->y + 1, $p->z + 1);
            $entities = $this->getLevel()->getNearbyEntities($area);
            for ($i = 0; $i < count($entities); ++$i) {
                $entity = $entities[$i];
                if (!($entity instanceof Living)) {
                    continue;
                }

                $id = $drop->getId();
                if ($id == 86 || $id == 298 || $id == 302 || $id == 306 || $id == 310 || $id == 314 || $id == 397 || $id == 469) {
                    $armor = $entity->getArmorInventory()->getHelmet();
                    if ($armor->getId() != 0) {
                        continue;
                    }
                    $entity->getArmorInventory()->setHelmet($drop);
                    $this->level->broadcastLevelEvent($this->add(0.5, 0.5, 0.5), LevelEventPacket::EVENT_SOUND_CLICK, 1000);
                    return;
                } else if ($id == 299 || $id == 303 || $id == 307 || $id == 311 || $id == 315) {
                    $armor = $entity->getArmorInventory()->getChestplate();
                    if ($armor->getId() != 0) {
                        continue;
                    }
                    $entity->getArmorInventory()->setChestplate($drop);
                    $this->level->broadcastLevelEvent($this->add(0.5, 0.5, 0.5), LevelEventPacket::EVENT_SOUND_CLICK, 1000);
                    return;
                } else if ($id == 300 || $id == 304 || $id == 308 || $id == 312 || $id == 316) {
                    $armor = $entity->getArmorInventory()->getLeggings();
                    if ($armor->getId() != 0) {
                        continue;
                    }
                    $entity->getArmorInventory()->setLeggings($drop);
                    $this->level->broadcastLevelEvent($this->add(0.5, 0.5, 0.5), LevelEventPacket::EVENT_SOUND_CLICK, 1000);
                    return;
                } else if ($id == 301 || $id == 305 || $id == 309 || $id == 313 || $id == 317) {
                    $armor = $entity->getArmorInventory()->getBoots();
                    if ($armor->getId() != 0) {
                        continue;
                    }
                    $entity->getArmorInventory()->setBoots($drop);
                    $this->level->broadcastLevelEvent($this->add(0.5, 0.5, 0.5), LevelEventPacket::EVENT_SOUND_CLICK, 1000);
                    return;
                }
            }
        }

        if ($drop->getId() == 46) {
            $mot = (new Random())->nextSignedFloat() * M_PI * 2;
            $nbt = Entity::createBaseNBT($pos, new Vector3(-sin($mot) * 0.02, 0.2, -cos($mot) * 0.02));
            $nbt->setShort("Fuse", 80);

            $tnt = Entity::createEntity("PrimedTNT", $this->getLevel(), $nbt);

            if($tnt !== null){
                $tnt->spawnToAll();
            }
            return;
        }

        if ($drop->getId() == 259) {
            $drop->applyDamage(1);
            $inventory->setItem($slot, $drop);
			$this->getLevel()->setBlock($this->getSide($damage), BlockFactory::get(Block::FIRE), true);
            $this->level->broadcastLevelEvent($this->add(0.5, 0.5, 0.5), LevelEventPacket::EVENT_SOUND_CLICK, 1000);
            return;
        }

        if ($drop->getId() == 262) {
            $motion = $motion->multiply(1.3);
            $nbt = Entity::createBaseNBT($pos, $motion, 0, 0);
            $entity = Entity::createEntity("Arrow", $this->getLevel(), $nbt, null, false);
            $entity->spawnToAll();
            $this->level->broadcastLevelEvent($this->add(0.5, 0.5, 0.5), LevelEventPacket::EVENT_SOUND_CLICK, 1000);
            return;
        }

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

        if ($drop->getId() == 351 && $drop->getDamage() == 15) {
            $side = $this->getLevel()->getBlock($this->getSide($damage));
            if ($side instanceof Crops || $side instanceof Sapling) {
                $side->onActivate($drop);
                $this->level->broadcastLevelEvent($this->add(0.5, 0.5, 0.5), LevelEventPacket::EVENT_SOUND_CLICK, 1000);
            } else {
                $item->setCount($item->getCount() + 1);
                $inventory->setItem($slot, $item);
                $this->level->broadcastLevelEvent($this->add(0.5, 0.5, 0.5), LevelEventPacket::EVENT_SOUND_CLICK_FAIL, 1200);
            }
            return;
        }

        if ($drop->getid() == 383) {
            $nbt = Entity::createBaseNBT($this->getSide($damage)->add(0.5, 0, 0.5), null, lcg_value() * 360, 0);

            if($drop->hasCustomName()){
                $nbt->setString("CustomName", $drop->getCustomName());
            }

            $entity = Entity::createEntity($drop->getDamage(), $this->getLevel(), $nbt);

            if($entity instanceof Entity){
                $entity->spawnToAll();
                return;
            }
        }

        if ($drop->getId() == 401) {
            $entity = Entity::createEntity("FireworksRocket", $this->getLevel(), Entity::createBaseNBT($pos, null, 0, 270), null, $drop);
            if($entity instanceof Entity) {
                $entity->spawnToAll();
                return;
            }
        }

        $this->level->dropItem($pos, $drop, $motion->multiply(0.3));
        $this->level->broadcastLevelEvent($this->add(0.5, 0.5, 0.5), LevelEventPacket::EVENT_SOUND_CLICK, 1000);
    }
}