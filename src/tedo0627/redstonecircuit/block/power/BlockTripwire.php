<?php

namespace tedo0627\redstonecircuit\block\power;

use pocketmine\block\Block;
use pocketmine\block\Tripwire;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class BlockTripwire extends Tripwire {

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool {
        $this->setSuspended(true);
        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

    public function onPostPlace(): void {
        $faces = [Facing::SOUTH, Facing::WEST];
        for ($i = 0; $i < count($faces); $i++) {
            $face = $faces[$i];
            for ($j = 1; $j < 41; $j++) {
                $block = $this->getSide($face, $j);
                if ($block instanceof BlockTripwire) continue;
                if ($block instanceof BlockTripwireHook && $block->getFacing() == Facing::opposite($face)) {
                    $block->tryConnect();
                }
                break;
            }
        }
    }

    public function onBreak(Item $item, ?Player $player = null): bool {
        parent::onBreak($item, $player);
        if (!$this->isConnected()) return true;

        $faces = [Facing::SOUTH, Facing::WEST];
        for ($i = 0; $i < count($faces); $i++) {
            $face = $faces[$i];
            for ($j = 1; $j < 41; $j++) {
                $block = $this->getSide($face, $j);
                if ($block instanceof BlockTripwire) continue;
                if ($block instanceof BlockTripwireHook && $block->getFacing() == Facing::opposite($face)) {
                    $block->disconnect($j, $item->getId() !== ItemIds::SHEARS);
                }
                break;
            }
        }
        return true;
    }

    public function onScheduledUpdate(): void {
        if ($this->isTriggered()) {
            $entities = $this->getPosition()->getWorld()->getNearbyEntities($this->getHitCollision());
            if (count($entities) > 0) return;

            $this->setTriggered(false);
            $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
            return;
        }

        $this->setConnected(false);
        $this->setSuspended(false);
        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
    }

    public function onEntityInside(Entity $entity): bool {
        $entities = $this->getPosition()->getWorld()->getNearbyEntities($this->getHitCollision());
        if (count($entities) <= 0) return true;

        $this->setTriggered(true);
        $world = $this->getPosition()->getWorld();
        $world->setBlock($this->getPosition(), $this);
        $world->scheduleDelayedBlockUpdate($this->getPosition(), 1);

        $faces = [Facing::SOUTH, Facing::WEST];
        for ($i = 0; $i < count($faces); $i++) {
            $face = $faces[$i];
            for ($j = 1; $j < 41; $j++) {
                $block = $this->getSide($face, $j);
                if ($block instanceof BlockTripwire) continue;
                if ($block instanceof BlockTripwireHook && $block->getFacing() == Facing::opposite($face)) {
                    $block->trigger();
                }
                break;
            }
        }
        return true;
    }

    public function hasEntityCollision(): bool {
        return true;
    }

    protected function getHitCollision(): AxisAlignedBB {
        return new AxisAlignedBB(
            $this->getPosition()->getX(),
            $this->getPosition()->getY(),
            $this->getPosition()->getZ(),
            $this->getPosition()->getX() + 1,
            $this->getPosition()->getY() + 0.0625,
            $this->getPosition()->getZ() + 1
        );
    }
}