<?php

namespace tedo0627\redstonecircuit\block\mechanism;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds as Ids;
use pocketmine\block\Opaque;
use pocketmine\block\utils\AnyFacingTrait;
use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use tedo0627\redstonecircuit\block\BlockPowerHelper;
use tedo0627\redstonecircuit\block\BlockUpdateHelper;
use tedo0627\redstonecircuit\block\entity\BlockEntityPistonArm;
use tedo0627\redstonecircuit\block\entity\IgnorePiston;
use tedo0627\redstonecircuit\block\ILinkRedstoneWire;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\LinkRedstoneWireTrait;
use tedo0627\redstonecircuit\block\PistonResolver;
use tedo0627\redstonecircuit\block\PistonTrait;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\event\BlockPistonExtendEvent;
use tedo0627\redstonecircuit\event\BlockPistonRetractEvent;
use tedo0627\redstonecircuit\RedstoneCircuit;
use tedo0627\redstonecircuit\sound\PistonInSound;
use tedo0627\redstonecircuit\sound\PistonOutSound;

class BlockPiston extends Opaque implements IRedstoneComponent, ILinkRedstoneWire {
    use AnyFacingTrait;
    use LinkRedstoneWireTrait;
    use RedstoneComponentTrait;
    use PistonTrait;

    protected function writeStateToMeta(): int {
        return BlockDataSerializer::writeFacing($this->facing);
    }

    public function readStateFromData(int $id, int $stateMeta): void {
        $this->setFacing(BlockDataSerializer::readFacing($stateMeta & 0x07));
    }

    public function readStateFromWorld(): void {
        parent::readStateFromWorld();
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        if (!$tile instanceof BlockEntityPistonArm) return;

        $this->setProgress($tile->getProgress());
        $this->setLastProgress($tile->getLastProgress());
        $this->setState($tile->getState());
        $this->setNewState($tile->getNewState());
        $this->setBreakBlocks($tile->getBreakBlocks());
        $this->setAttachedBlocks($tile->getAttachedBlocks());
        $this->setHideAttachedBlocks($tile->getHideAttachedBlocks());
    }

    public function writeStateToWorld(): void {
        parent::writeStateToWorld();
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        assert($tile instanceof BlockEntityPistonArm);

        $tile->setProgress($this->getProgress());
        $tile->setLastProgress($this->getLastProgress());
        $tile->setState($this->getState());
        $tile->setNewState($this->getNewState());
        $tile->setSticky($this->isSticky());
        $tile->setBreakBlocks($this->getBreakBlocks());
        $tile->setAttachedBlocks($this->getAttachedBlocks());
        $tile->setHideAttachedBlocks($this->getHideAttachedBlocks());
    }

    public function getStateBitmask(): int {
        return 0b1111;
    }

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool {
        if ($player !== null) {
            $x = abs($player->getLocation()->getFloorX() - $this->getPosition()->getX());
            $y = $player->getLocation()->getFloorY() - $this->getPosition()->getY();
            $z = abs($player->getLocation()->getFloorZ() - $this->getPosition()->getZ());
            if ($y > 0 && $x < 2 && $z < 2) {
                $this->setFacing(Facing::UP);
            } elseif ($y < -1 && $x < 2 && $z < 2) {
                $this->setFacing(Facing::DOWN);
            } else {
                $this->setFacing($player->getHorizontalFacing());
            }
        }
        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

    public function onBreak(Item $item, ?Player $player = null): bool {
        parent::onBreak($item, $player);
        $block = $this->getSide($this->getPistonArmFace());
        if ($block instanceof BlockPistonArmCollision && $this->getFacing() === $block->getFacing()) {
            $this->getPosition()->getWorld()->useBreakOn($block->getPosition());
        }
        return true;
    }

    public function onScheduledUpdate(): void {
        if (BlockPowerHelper::isPowered($this, $this->getPistonArmFace())) {
            if (!$this->push()) return;
        } else {
            if (!$this->pull()) return;
        }
        $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 1);
    }

    public function asItem(): Item {
        return ItemFactory::getInstance()->get($this->idInfo->getItemId(), 1);
    }

    public function onRedstoneUpdate(): void {
        $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 1);
    }

    public function isSticky(): bool {
        return false;
    }

    public function getNewPistonArm(): Block {
        return BlockFactory::getInstance()->get(Ids::PISTONARMCOLLISION, $this->getFacing());
    }

    public function getPistonArmFace(): int {
        $face = $this->getFacing();
        return Facing::axis($face) === Axis::Y ? $face : Facing::opposite($face);
    }

    private function push(): bool {
        $state = $this->getState();
        if ($state === 0) {
            $resolver = new PistonResolver($this, $this->isSticky(), true);
            $resolver->resolve();
            if (!$resolver->isSuccess()) return false;

            if (RedstoneCircuit::isCallEvent()) {
                $event = new BlockPistonExtendEvent($this, $resolver->getAttachBlocks(), $resolver->getBreakBlocks());
                $event->call();
                if ($event->isCancelled()) return false;
            }

            foreach ($resolver->getBreakBlocks() as $block) {
                $this->addBreakBlock($block);
            }
            foreach ($resolver->getAttachBlocks() as $block) {
                $this->addAttachedBlock($block);
            }

            $this->setState(1);
            $this->setNewState(1);

            $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
        } else if ($state === 1) {
            $face = $this->getPistonArmFace();
            $side = $this->getSide($face);
            $arm = $this->getPosition()->getWorld()->getBlock($side->getPosition());
            if (!($arm instanceof BlockPistonArmCollision)) {
                $resolver = new PistonResolver($this, $this->isSticky(), true);
                $resolver->resolve();
                if (!$resolver->isSuccess()) {
                    $this->setState(0);
                    $this->setNewState(0);
                    $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
                    return false;
                }

                if (RedstoneCircuit::isCallEvent()) {
                    $event = new BlockPistonExtendEvent($this, $resolver->getAttachBlocks(), $resolver->getBreakBlocks());
                    $event->call();
                    if ($event->isCancelled()) {
                        $this->setState(0);
                        $this->setNewState(0);
                        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
                        return false;
                    }
                }

                $this->setBreakBlocks([]);
                $this->setAttachedBlocks([]);

                $world = $this->getPosition()->getWorld();
                foreach ($resolver->getBreakBlocks() as $block) {
                    $world->useBreakOn($block->getPosition());
                }
                foreach ($resolver->getAttachBlocks() as $block) {
                    $attachSide = $block->getSide($face);
                    $this->addHideAttachedBlock($block);
                    $moving = BlockFactory::getInstance()->get(Ids::MOVINGBLOCK, 0);
                    $tile = $world->getTile($block->getPosition());
                    if ($tile instanceof IgnorePiston) $tile = null;
                    if ($moving instanceof BlockMoving) {
                        $moving->setExpanding(true);
                        $moving->setMovingBlock($block, $tile);
                        $moving->setPistonPos($this);
                    }
                    $world->setBlock($attachSide->getPosition(), $moving);
                    $world->setBlock($block->getPosition(), VanillaBlocks::AIR());
                    BlockUpdateHelper::updateAroundRedstone($block);
                }

                $this->getPosition()->getWorld()->setBlock($side->getPosition(), $this->getNewPistonArm());
                $this->writeStateToWorld();
                return true;
            }

            $this->setState(2);
            $this->setNewState(2);
            $this->setProgress(1.0);
            $this->setLastProgress(1.0);
            $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
            $this->getPosition()->getWorld()->addSound($this->getPosition()->add(0.5, 0.5, 0.5), new PistonOutSound());
        } else if ($state === 2) {
            $attached = $this->getHideAttachedBlocks();
            $world = $this->getPosition()->getWorld();
            $face = $this->getPistonArmFace();
            for ($i = 0; $i < count($attached); $i += 3) {
                $x = $attached[$i];
                $y = $attached[$i + 1];
                $z = $attached[$i + 2];
                $moving = $world->getBlockAt($x, $y, $z)->getSide($face);
                $pos = $moving->getPosition();
                if ($moving instanceof BlockMoving) {
                    $world->setBlock($pos, $moving->getMovingBlock());
                    $tile = $world->getTile($pos);
                    $tag = $moving->getMovingEntity();
                    if ($tile !== null && $tag !== null) $tile->readSaveData($tag);

                    $block = $world->getBlock($pos);
                    if ($block instanceof IRedstoneComponent) $block->onRedstoneUpdate();
                    BlockUpdateHelper::updateAroundRedstone($block);
                }
            }

            $this->setHideAttachedBlocks([]);
            $this->writeStateToWorld();
            return false;
        } else if ($state === 3) return $this->pull();
        else return false;
        return true;
    }

    private function pull(): bool {
        $state = $this->getState();
        if ($state === 2) {
            $resolver = new PistonResolver($this, $this->isSticky(), false);
            $resolver->resolve();
            if (!$resolver->isSuccess()) return false;

            if (RedstoneCircuit::isCallEvent()) {
                $event = new BlockPistonRetractEvent($this, $resolver->getAttachBlocks(), $resolver->getBreakBlocks());
                $event->call();
                if ($event->isCancelled()) return false;
            }

            foreach ($resolver->getBreakBlocks() as $block) {
                $this->addBreakBlock($block);
            }
            foreach ($resolver->getAttachBlocks() as $block) {
                $this->addAttachedBlock($block);
            }

            $this->setState(3);
            $this->setNewState(3);

            $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
        } else if ($state === 3) {
            $face = $this->getPistonArmFace();
            $side = $this->getSide($face);
            $arm = $this->getPosition()->getWorld()->getBlock($side->getPosition());
            if ($arm instanceof BlockPistonArmCollision) {
                $resolver = new PistonResolver($this, $this->isSticky(), false);
                $resolver->resolve();
                if (!$resolver->isSuccess()) {
                    $this->setState(2);
                    $this->setNewState(2);
                    $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
                    return false;
                }

                if (RedstoneCircuit::isCallEvent()) {
                    $event = new BlockPistonRetractEvent($this, $resolver->getAttachBlocks(), $resolver->getBreakBlocks());
                    $event->call();
                    if ($event->isCancelled()) {
                        $this->setState(2);
                        $this->setNewState(2);
                        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
                        return false;
                    }
                }

                $this->setBreakBlocks([]);
                $this->setAttachedBlocks([]);

                $this->getPosition()->getWorld()->setBlock($side->getPosition(), VanillaBlocks::AIR());

                $world = $this->getPosition()->getWorld();
                foreach ($resolver->getBreakBlocks() as $block) {
                    $world->useBreakOn($block->getPosition());
                }
                $face = Facing::opposite($face);
                foreach ($resolver->getAttachBlocks() as $block) {
                    $side = $block->getSide($face);
                    $this->addHideAttachedBlock($block);
                    $moving = BlockFactory::getInstance()->get(Ids::MOVINGBLOCK, 0);
                    $tile = $world->getTile($block->getPosition());
                    if ($tile instanceof IgnorePiston) $tile = null;
                    if ($moving instanceof BlockMoving) {
                        $moving->setExpanding(false);
                        $moving->setMovingBlock($block, $tile);
                        $moving->setPistonPos($this);
                    }
                    $world->setBlock($side->getPosition(), $moving);
                    $world->setBlock($block->getPosition(), VanillaBlocks::AIR());
                    BlockUpdateHelper::updateAroundRedstone($block);
                }
                $this->writeStateToWorld();
                return true;
            }

            $this->setState(0);
            $this->setNewState(0);
            $this->setProgress(0.0);
            $this->setLastProgress(0.0);
            $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
            $this->getPosition()->getWorld()->addSound($this->getPosition()->add(0.5, 0.5, 0.5), new PistonInSound());
        } else if ($state === 0) {
            $attached = $this->getHideAttachedBlocks();
            $world = $this->getPosition()->getWorld();
            $face = Facing::opposite($this->getPistonArmFace());
            for ($i = 0; $i < count($attached); $i += 3) {
                $x = $attached[$i];
                $y = $attached[$i + 1];
                $z = $attached[$i + 2];
                $moving = $world->getBlockAt($x, $y, $z)->getSide($face);
                $pos = $moving->getPosition();
                if ($moving instanceof BlockMoving) {
                    $world->setBlock($pos, $moving->getMovingBlock());
                    $tile = $world->getTile($pos);
                    $tag = $moving->getMovingEntity();
                    if ($tile !== null && $tag !== null) $tile->readSaveData($tag);

                    $block = $world->getBlock($pos);
                    if ($block instanceof IRedstoneComponent) $block->onRedstoneUpdate();
                    BlockUpdateHelper::updateAroundRedstone($block);
                }
            }

            $this->setHideAttachedBlocks([]);
            $this->writeStateToWorld();
            return false;
        } else if ($state === 1) return $this->push();
        else return false;
        return true;
    }
}