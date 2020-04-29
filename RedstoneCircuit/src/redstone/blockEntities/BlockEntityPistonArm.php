<?php

namespace redstone\blockEntities;

use pocketmine\block\Block;
use pocketmine\block\Flowable;

use pocketmine\math\Vector3;

use pocketmine\nbt\NBT;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\IntTag;

use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

use pocketmine\tile\Spawnable;

use redstone\Main;

use redstone\blocks\BlockMoving;
use redstone\blocks\BlockPistonarmcollision;
use redstone\blocks\IRedstone;

use redstone\utils\Facing;

use function count;

class BlockEntityPistonArm extends Spawnable {

    protected $progress = 0;
    protected $lastProgress = 0;

    protected $state = 0;
    protected $newState = 0;

    protected $sticky = 0;

    protected $breakBlocks = [];
    protected $attachedBlocks = [];

    protected $extend = false;

    protected function readSaveData(CompoundTag $nbt) : void {
        if ($nbt->hasTag("Progress")) {
            $this->progress = $nbt->getFloat("Progress");
        }
        if ($nbt->hasTag("LastProgress")) {
            $this->lastProgress = $nbt->getFloat("LastProgress");
        }

        if ($nbt->hasTag("State")) {
            $this->state = $nbt->getByte("State");
        }
        if ($nbt->hasTag("NewState")) {
            $this->newState = $nbt->getByte("NewState");
        }

        if ($nbt->hasTag("Sticky")) {
            $this->sticky = $nbt->getByte("Sticky");
        }

        if ($nbt->hasTag("BreakBlocks")) {
            $tag = $nbt->getListTag("BreakBlocks");
            $this->breakBlocks = $tag->getValue();
        }
        if ($nbt->hasTag("AttchedBlocks")) {
            $tag = $nbt->getListTag("AttchedBlocks");
            $this->attachedBlocks = $tag->getValue();
        }

        if ($nbt->hasTag("Extend")) {
            $this->extend = $nbt->getByte("Extend") == 1 ? true : false;
        }

        $this->scheduleUpdate();
    }

    protected function writeSaveData(CompoundTag $nbt) : void {
        $nbt->setFloat("Progress", $this->progress);
        $nbt->setFloat("LastProgress", $this->lastProgress);

        $nbt->setByte("State", $this->state);
        $nbt->setByte("NewState", $this->newState);

        $nbt->setByte("Sticky", $this->sticky);

        $nbt->setTag(new ListTag("BreakBlocks", $this->breakBlocks, NBT::TAG_Int));
        $nbt->setTag(new ListTag("AttchedBlocks", $this->attachedBlocks, NBT::TAG_Int));

        $nbt->setByte("Extend", $this->extend ? 1 : 0);
    }

    public function getName() : string{
        return "PistonArm";
    }

    public function onUpdate() : bool {
        if ($this->isClosed()) {
            return false;
        }

        if ($this->extend) {
            if ($this->newState == 0) {
                $piston = $this->getBlock();
                $side = $this->getSide($piston->getFace());
                if ($this->getLevel()->getBlock($side)->getId() != 0) {
                    $blocks = $this->recalculatePushBlocks();
                    if (count($blocks) == 0) {
                        $this->extend = false;
                        return true;
                    }

                    $face = $piston->getFace();
                    for ($i = 0; $i < count($blocks); ++$i) {
                        $block = $blocks[$i];
                        $pos = $block->asVector3()->getSide($face);
                        if ($this->isBreakBlock($block)) {
                            $this->getLevel()->useBreakOn($block);
                            $this->breakBlocks[] = new IntTag("", $pos->getX());
                            $this->breakBlocks[] = new IntTag("", $pos->getY());
                            $this->breakBlocks[] = new IntTag("", $pos->getZ());
                        } else {
                            $this->getLevel()->setBlock($pos, new BlockMoving());
                            $tile = $this->getLevel()->getTile($block);
                            $this->getLevel()->setBlock($block, Block::get(0));
                            $this->updateAroundRedstone($block->asVector3());
                            $this->getLevel()->getBlock($pos)->setData($piston, $block, $tile);
                            if ($tile != null) {
                                $this->getLevel()->removeTile($tile);
                            }

                            $this->attachedBlocks[] = new IntTag("", $pos->getX());
                            $this->attachedBlocks[] = new IntTag("", $pos->getY());
                            $this->attachedBlocks[] = new IntTag("", $pos->getZ());
                        }
                    }
                }

                $this->getLevel()->setBlock($side, new BlockPistonarmcollision($piston->getDamage()));

                $this->newState = 1;
                $this->onChanged();
                return true;
            }

            if ($this->newState == 1) {
                if ($this->state == 0) {
                    $this->state = 1;
                }

                $this->lastProgress = $this->progress;
                if ($this->progress == 1) {
                    $this->state = 2;
                    $this->newState = 2;
                } else {
                    $this->progress += 0.5;
                }

                $this->onChanged();

                if ($this->progress == 0.5) {
                    $pk = new LevelSoundEventPacket();
                    $pk->sound = 84;
                    $pk->position = $this;
                    $pk->extraData = -1;
                    $pk->entityType = ":";
                    $pk->isBabyMob = false;
                    $pk->disableRelativeVolume = false;
                    $this->getLevel()->addChunkPacket($this->getFloorX() >> 4, $this->getFloorZ() >> 4, $pk);
                } else if ($this->progress == 1.0) {
                    for ($i = 0; $i < count($this->attachedBlocks); $i += 3) {
                        $x = $this->attachedBlocks[$i]->getValue();
                        $y = $this->attachedBlocks[$i + 1]->getValue();
                        $z = $this->attachedBlocks[$i + 2]->getValue();
                        $pos = new Vector3($x, $y, $z);

                        $block = $this->getLevel()->getBlock($pos);
                        if ($block instanceof BlockMoving) {
                            $block->setMovedBlock();
                            $block = $this->getLevel()->getBlock($pos);
                            if ($block instanceof IRedstone) {
                                $block->onRedstoneUpdate();
                            }
                            $this->updateAroundRedstone($pos);
                        }
                    }
                    $this->attachedBlocks = [];
                    $this->breakBlocks = [];
                }
            }

            if ($this->newState == 3) {
                $this->lastProgress = 0;
                $this->progress = 0;

                for ($i = 0; $i < count($this->attachedBlocks); $i += 3) {
                    $x = $this->attachedBlocks[$i]->getValue();
                    $y = $this->attachedBlocks[$i + 1]->getValue();
                    $z = $this->attachedBlocks[$i + 2]->getValue();
                    $pos = new Vector3($x, $y, $z);

                    $block = $this->getLevel()->getBlock($pos);
                    if ($block instanceof BlockMoving) {
                        $block->setMovedBlock();
                        $block = $this->getLevel()->getBlock($pos);
                        if ($block instanceof IRedstone) {
                            $block->onRedstoneUpdate();
                        }
                        $this->updateAroundRedstone($block->asVector3());
                    }
                }
                $this->attachedBlocks = [];
                $this->breakBlocks = [];

                $this->state = 0;
                $this->newState = 0;

                $this->onChanged();
            }
        } else {
            if ($this->state == 2) {
                $piston = $this->getBlock();

                $side = $this->getSide($piston->getFace());
                $this->getLevel()->setBlock($side, Block::get(0));

                if ($this->isSticky()) {
                    $side = $this->getSide($piston->getFace(), 2);
                    if ($this->getLevel()->getBlock($side)->getId() != 0) {
                        $blocks = $this->recalculatePullBlocks();
                        if (count($blocks) != 0) {
                            $face = Facing::opposite($piston->getFace());
                            for ($i = 0; $i < count($blocks); ++$i) {
                                $block = $blocks[$i];
                                $pos = $block->asVector3()->getSide($face);
                                if ($this->isBreakBlock($block)) {
                                    $this->getLevel()->useBreakOn($block);
                                    $this->breakBlocks[] = new IntTag("", $pos->getX());
                                    $this->breakBlocks[] = new IntTag("", $pos->getY());
                                    $this->breakBlocks[] = new IntTag("", $pos->getZ());
                                } else {
                                    $this->getLevel()->setBlock($pos, new BlockMoving());
                                    $tile = $this->getLevel()->getTile($block);
                                    $this->getLevel()->setBlock($block, Block::get(0));
                                    $this->updateAroundRedstone($block->asVector3());
                                    $this->getLevel()->getBlock($pos)->setData($piston, $block, $tile);
                                    if ($tile != null) {
                                        $this->getLevel()->removeTile($tile);
                                    }

                                    $this->attachedBlocks[] = new IntTag("", $pos->getX());
                                    $this->attachedBlocks[] = new IntTag("", $pos->getY());
                                    $this->attachedBlocks[] = new IntTag("", $pos->getZ());
                                }
                            }
                        }
                    }
                }

                $this->state = 3;
                $this->newState = 3;

                $this->onChanged();
                return true;
            }

            if ($this->newState == 1) {
                $this->lastProgress = 1;
                $this->progress = 1;

                for ($i = 0; $i < count($this->attachedBlocks); $i += 3) {
                    $x = $this->attachedBlocks[$i]->getValue();
                    $y = $this->attachedBlocks[$i + 1]->getValue();
                    $z = $this->attachedBlocks[$i + 2]->getValue();
                    $pos = new Vector3($x, $y, $z);

                    $block = $this->getLevel()->getBlock($pos);
                    if ($block instanceof BlockMoving) {
                        $block->setMovedBlock();
                        $block = $this->getLevel()->getBlock($pos);
                        if ($block instanceof IRedstone) {
                            $block->onRedstoneUpdate();
                        }
                        $this->updateAroundRedstone($block->asVector3());
                    }
                }
                $this->attachedBlocks = [];
                $this->breakBlocks = [];

                $this->state = 2;
                $this->newState = 2;

                $this->onChanged();
            }

            if ($this->state == 3) {
                $this->lastProgress = $this->progress;
                if ($this->progress == 0) {
                    $this->state = 0;
                    $this->newState = 0;
                } else {
                    $this->progress -= 0.5;
                }

                $this->onChanged();

                if ($this->progress == 0.5) {
                    $pk = new LevelSoundEventPacket();
                    $pk->sound = 83;
                    $pk->position = $this;
                    $pk->extraData = -1;
                    $pk->entityType = ":";
                    $pk->isBabyMob = false;
                    $pk->disableRelativeVolume = false;
                    $this->getLevel()->addChunkPacket($this->getFloorX() >> 4, $this->getFloorZ() >> 4, $pk);
                } else if ($this->progress == 0) {
                    for ($i = 0; $i < count($this->attachedBlocks); $i += 3) {
                        $x = $this->attachedBlocks[$i]->getValue();
                        $y = $this->attachedBlocks[$i + 1]->getValue();
                        $z = $this->attachedBlocks[$i + 2]->getValue();
                        $pos = new Vector3($x, $y, $z);

                        $block = $this->getLevel()->getBlock($pos);
                        if ($block instanceof BlockMoving) {
                            $block->setMovedBlock();
                            $block = $this->getLevel()->getBlock($pos);
                            if ($block instanceof IRedstone) {
                                $block->onRedstoneUpdate();
                            }
                            $this->updateAroundRedstone($pos);
                        }
                    }
                    $this->attachedBlocks = [];
                    $this->breakBlocks = [];
                }
            }
        }

        return true;
    }

    private function updateAroundRedstone(Vector3 $pos) : void {
        $direction = Facing::ALL;
        for ($i = 0; $i < count($direction); ++$i) {
            $block = $this->getLevel()->getBlock($pos->getSide($direction[$i]));
            if ($block instanceof IRedstone) {
                $block->onRedstoneUpdate();
            }
        }
    }

    protected function addAdditionalSpawnData(CompoundTag $nbt) : void {
        $nbt->setFloat("Progress", $this->progress);
        $nbt->setFloat("LastProgress", $this->lastProgress);
        $nbt->setByte("State", $this->state);
        $nbt->setByte("NewState", $this->newState);
        $nbt->setByte("Sticky", $this->sticky);
        $nbt->setTag(new ListTag("BreakBlocks", $this->breakBlocks, NBT::TAG_Int));
        $nbt->setTag(new ListTag("AttachedBlocks", $this->attachedBlocks, NBT::TAG_Int));
    }

    public function extend(bool $extend) : void {
        $this->extend = $extend;
    }

    public function isExtends() : bool {
        return $this->extend;
    }

    public function getProgress() : int {
        return $this->progress;
    }

    public function getLastProgress() : int {
        return $this->lastProgress;
    }

    public function getState() : int {
        return $this->state;
    }

    public function getNewState() : int {
        return $this->newState;
    }

    public function isSticky() : bool {
        return $this->sticky == 1;
    }

    private function recalculatePushBlocks() : array {
        $breaks = [];
        $blocks = [];

        $max = Main::getInstance()->getCustomConfig()->getMaxPistonPushBlocks();

        $block = $this->getBlock();
        $face = $block->getFace();
        $queue = $this->createQueue();
        $queue->add($this->createData($this->getSide($face), 1, $face));

        while (!$queue->isEmpty()) {
            $data = $queue->poll();
            $pos = $data->pos;
            $block = $this->getLevel()->getBlock($pos);

            if ($block->getId() == 0) {
                continue;
            }

            if (!$this->canBeMoved($block)) {
                if ($face == $data->face) {
                    return [];
                } else {
                    continue;
                }
            }

            if ($this->isBreakBlock($block)) {
                if ($face == $data->face) {
                    $breaks[] = $block;
                }
                continue;
            }

            if ($face == Facing::UP && $pos->y >= 255) {
                return [];
            }

            if ($face == Facing::DOWN && $pos->y <= 0) {
                return [];
            }

            if (array_search($block, $blocks) !== false) {
                continue;
            }
            $blocks[] = $block;

            if (count($blocks) > $max) {
                return [];
            }

            if ($block->getId() == Block::SLIME) {
                $directions = Facing::ALL;
                for ($i = 0; $i < count($directions); ++$i) {
                    $direction = $directions[$i];
                    if ($direction == Facing::opposite($data->face)) {
                        continue;
                    }
                    $queue->add($this->createData($pos->getSide($direction), $data->distance + 1, $direction));
                }
            } else {
                $queue->add($this->createData($pos->getSide($face), $data->distance + 1, $face));
            }
        }

        $blocks = array_merge($blocks, $breaks);
        usort($blocks, function($a, $b) {
            $v = new Vector3();
            $v = $v->getSide($this->getBlock()->getFace());
            if ($v->x > 0) return $b->x - $a->x;
            if ($v->x < 0) return $a->x - $b->x;
            if ($v->y > 0) return $b->y - $a->y;
            if ($v->y < 0) return $a->y - $b->y;
            if ($v->z > 0) return $b->z - $a->z;
            if ($v->z < 0) return $a->z - $b->z;
        });
        return $blocks;
    }

    private function recalculatePullBlocks() : array {
        $breaks = [];
        $blocks = [];

        $max = Main::getInstance()->getCustomConfig()->getMaxPistonPushBlocks();

        $block = $this->getBlock();
        $face = $block->getFace();
        $queue = $this->createQueue();
        $queue->add($this->createData($this->getSide($face, 2), 1, $face));

        while (!$queue->isEmpty()) {
            $data = $queue->poll();
            $pos = $data->pos;
            $block = $this->getLevel()->getBlock($pos);

            if ($block->getId() == 0) {
                continue;
            }

            if (!$this->canBeMoved($block)) {
                if ($face == Facing::opposite($data->face)) {
                    return [];
                } else {
                    continue;
                }
            }

            if ($this->isBreakBlock($block)) {
                if ($face == Facing::opposite($data->face)) {
                    $breaks[] = $block;
                }
                continue;
            }

            if (array_search($block, $blocks) !== false) {
                continue;
            }
            $blocks[] = $block;

            if (count($blocks) > $max) {
                return [];
            }

            if ($block->getId() != Block::SLIME) {
                continue;
            }

            $directions = Facing::ALL;
            for ($i = 0; $i < count($directions); ++$i) {
                $direction = $directions[$i];
                if ($direction == Facing::opposite($data->face)) {
                    continue;
                }
                $queue->add($this->createData($pos->getSide($direction), $data->distance + 1, $direction));
            }
        }

        $blocks = array_merge($blocks, $breaks);
        usort($blocks, function($a, $b) {
            $v = new Vector3();
            $v = $v->getSide(Facing::opposite($this->getBlock()->getFace()));
            if ($v->x > 0) return $b->x - $a->x;
            if ($v->x < 0) return $a->x - $b->x;
            if ($v->y > 0) return $b->y - $a->y;
            if ($v->y < 0) return $a->y - $b->y;
            if ($v->z > 0) return $b->z - $a->z;
            if ($v->z < 0) return $a->z - $b->z;
        });
        return $blocks;
    }

    private function createQueue() {
        return new class {
            private $data = [];

            public function add($object) : void {
                array_push($this->data, $object);
                usort($this->data, function($a, $b) {
                    return $a->distance - $b->distance;
                });
            }

            public function poll() {
                return array_shift($this->data);
            }

            public function isEmpty() : bool {
                return count($this->data) == 0;
            }
        };
    }

    private function createData(Vector3 $pos, int $distance, int $face) {
        return new class($pos, $distance, $face) {
            public $pos;
            public $distance;
            public $face;

            public function __construct(Vector3 $pos, int $distance, int $face) {
                $this->pos = $pos;
                $this->distance = $distance;
                $this->face = $face;
            }
        };
    }

    private function canBeMoved(Block $block) : bool {
        $id = $block->getId();
        if ($id == Block::PISTON || $id == Block::STICKY_PISTON) {
            $piston = $block->getBlockEntity();
            if ($piston->getState() == 0 && !$piston->isExtends()) {
                return true;
            }
            return false;
        }

        $ids = [Block::BEDROCK, Block::PISTONARMCOLLISION, Block::OBSIDIAN, Block::MOB_SPAWNER, Block::PORTAL, Block::INVISIBLEBEDROCK, Block::ENCHANTING_TABLE, Block::END_PORTAL, Block::END_PORTAL_FRAME, Block::ENDER_CHEST, Block::COMMAND_BLOCK, Block::BEACON, Block::STANDING_BANNER, Block::WALL_BANNER, Block::REPEATING_COMMAND_BLOCK, Block::CHAIN_COMMAND_BLOCK, Block::END_GATEWAY, Block::STRUCTURE_BLOCK];
        return array_search($id, $ids) === false;
    }

    private function isBreakBlock(Block $block) : bool {
        if ($block instanceof Flowable) {
            return true;
        }

        $ids = [Block::FLOWING_WATER, Block::STILL_WATER, Block::FLOWING_LAVA, Block::STILL_LAVA, Block::LEAVES, Block::BED_BLOCK, Block::COBWEB, Block::FIRE, Block::STANDING_SIGN, Block::LADDER. Block::WALL_SIGN, Block::LEVER, Block::STONE_PRESSURE_PLATE, Block::WOODEN_PRESSURE_PLATE, Block::STONE_BUTTON, Block::SNOW_LAYER, Block::CACTUS, Block::PUMPKIN, Block::JACK_O_LANTERN, Block::MELON_BLOCK, Block::VINE, Block::WATER_LILY, Block::DRAGON_EGG, Block::COCOA, Block::FLOWER_POT_BLOCK, Block::WOODEN_BUTTON, Block::SKULL_BLOCK, Block::LIGHT_WEIGHTED_PRESSURE_PLATE, Block::HEAVY_WEIGHTED_PRESSURE_PLATE, Block::LEAVES2, Block::ITEM_FRAME_BLOCK, Block::CHORUS_FLOWER, Block::UNDYED_SHULKER_BOX, Block::SHULKER_BOX, Block::CHORUS_PLANT];
        return array_search($block->getId(), $ids) !== false;
    }
}