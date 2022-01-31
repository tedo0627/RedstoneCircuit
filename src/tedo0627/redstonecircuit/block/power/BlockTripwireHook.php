<?php

namespace tedo0627\redstonecircuit\block\power;

use pocketmine\block\Block;
use pocketmine\block\TripwireHook;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\RedstonePowerOffSound;
use pocketmine\world\sound\RedstonePowerOnSound;
use tedo0627\redstonecircuit\block\BlockUpdateHelper;
use tedo0627\redstonecircuit\block\FlowablePlaceHelper;
use tedo0627\redstonecircuit\block\ILinkRedstoneWire;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\LinkRedstoneWireTrait;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\sound\AttachSound;
use tedo0627\redstonecircuit\sound\DetachSound;

class BlockTripwireHook extends TripwireHook implements IRedstoneComponent, ILinkRedstoneWire {
    use LinkRedstoneWireTrait;
    use RedstoneComponentTrait;

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool {
        if (!FlowablePlaceHelper::checkSurface($this, Facing::opposite($face))) return false;
        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

    public function onPostPlace(): void {
        $this->tryConnect();
    }

    public function onBreak(Item $item, ?Player $player = null): bool {
        parent::onBreak($item, $player);
        $this->disconnect();
        return true;
    }

    public function onNearbyBlockChange(): void {
        if (FlowablePlaceHelper::checkSurface($this, Facing::opposite($this->getFacing()))) return;
        $this->getPosition()->getWorld()->useBreakOn($this->getPosition());
    }

    public function onScheduledUpdate(): void {
        $triggered = false;
        for ($i = 1; $i < 42; $i++) {
            $block = $this->getSide($this->getFacing(), $i);
            if ($block instanceof BlockTripwire) {
                if ($block->isTriggered()) $triggered = true;
                continue;
            }

            $world = $this->getPosition()->getWorld();
            if (!$block instanceof BlockTripwireHook || $block->getFacing() !== Facing::opposite($this->getFacing())) {
                $sound = $this->isPowered() ? new RedstonePowerOffSound() : new DetachSound();
                $world->addSound($this->getPosition()->add(0.5, 0.5, 0.5), $sound);
                $this->setConnected(false);
                $this->setPowered(false);
                $world->setBlock($this->getPosition(), $this);
                BlockUpdateHelper::updateAroundDirectionRedstone($this, Facing::opposite($this->getFacing()));
                break;
            }

            if (!$triggered) {
                $this->setPowered(false);
                $world->setBlock($this->getPosition(), $this);
                $world->addSound($this->getPosition()->add(0.5, 0.5, 0.5), new RedstonePowerOffSound());
                BlockUpdateHelper::updateAroundDirectionRedstone($this, Facing::opposite($this->getFacing()));
                break;
            }

            $world->scheduleDelayedBlockUpdate($this->getPosition(), 1);
            break;
        }
        /*
        $this->setConnected(false);
        $this->setPowered(false);
        $world = $this->getPosition()->getWorld();
        $world->setBlock($this->getPosition(), $this);
        $world->addSound($this->getPosition()->add(0.5, 0.5, 0.5), new RedstonePowerOffSound());
        BlockUpdateHelper::updateAroundDirectionRedstone($this, Facing::opposite($this->getFacing()));
        */
    }

    public function tryConnect(): void {
        /** @var BlockTripwire[] $blocks */
        $blocks = [];
        for ($i = 1; $i < 42; $i++) {
            $block = $this->getSide($this->getFacing(), $i);
            if ($block instanceof BlockTripwire) {
                $blocks[] = $block;
                continue;
            }

            if (!$block instanceof BlockTripwireHook) break;
            if ($block->getFacing() !== Facing::opposite($this->getFacing())) break;

            $this->setConnected(true);
            $world = $this->getPosition()->getWorld();
            $world->setBlock($this->getPosition(), $this);
            $world->addSound($this->getPosition()->add(0.5, 0.5, 0.5), new AttachSound());

            $block->setConnected(true);
            $world->setBlock($block->getPosition(), $block);
            $world->addSound($block->getPosition()->add(0.5, 0.5, 0.5), new AttachSound());

            for ($j = 0; $j < count($blocks); $j++) {
                $tripwire = $blocks[$j];
                $tripwire->setConnected(true);
                $world->setBlock($tripwire->getPosition(), $tripwire);
            }
            break;
        }
    }

    public function disconnect(int $step = 0, bool $powered = false): void {
        /** @var BlockTripwire[] $blocks */
        $blocks = [];
        for ($i = 1; $i < 42; $i++) {
            if ($step === $i) continue;

            $block = $this->getSide($this->getFacing(), $i);
            if ($block instanceof BlockTripwire) {
                $blocks[] = $block;
                continue;
            }

            if (!$block instanceof BlockTripwireHook) break;
            if ($block->getFacing() !== Facing::opposite($this->getFacing())) break;

            $world = $this->getPosition()->getWorld();
            $block->setConnected(false);
            if ($step === 0) {
                $block->setPowered(false);
                $world->setBlock($block->getPosition(), $block);

                $world->addSound($this->getPosition()->add(0.5, 0.5, 0.5), new DetachSound());
                $world->addSound($block->getPosition()->add(0.5, 0.5, 0.5), new DetachSound());

                for ($j = 0; $j < count($blocks); $j++) {
                    $tripwire = $blocks[$j];
                    $tripwire->setConnected(false);
                    $tripwire->setSuspended(false);
                    $world->setBlock($tripwire->getPosition(), $tripwire);
                }
                return;
            }

            if ($powered) {
                $this->setPowered(true);
                $world->setBlock($this->getPosition(), $this);
                BlockUpdateHelper::updateAroundDirectionRedstone($this, Facing::opposite($this->getFacing()));
                $world->addSound($this->getPosition()->add(0.5, 0.5, 0.5), new RedstonePowerOnSound());

                $block->setPowered(true);
                $world->setBlock($block->getPosition(), $block);
                BlockUpdateHelper::updateAroundDirectionRedstone($block, Facing::opposite($block->getFacing()));
                $world->addSound($block->getPosition()->add(0.5, 0.5, 0.5), new RedstonePowerOnSound());
            }
            $world->scheduleDelayedBlockUpdate($this->getPosition(), 10);
            $world->scheduleDelayedBlockUpdate($block->getPosition(), 10);

            for ($j = 0; $j < count($blocks); $j++) {
                $tripwire = $blocks[$j];
                $world->scheduleDelayedBlockUpdate($tripwire->getPosition(), 10);
            }
            break;
        }
    }

    public function trigger(): void {
        if ($this->isPowered()) return;

        $triggered = false;
        for ($i = 1; $i < 42; $i++) {
            $block = $this->getSide($this->getFacing(), $i);
            if ($block instanceof BlockTripwire) {
                if ($block->isTriggered()) $triggered = true;
                continue;
            }

            if (!$block instanceof BlockTripwireHook) break;
            if ($block->getFacing() !== Facing::opposite($this->getFacing())) break;
            if (!$triggered) break;

            $this->setPowered(true);
            $world = $this->getPosition()->getWorld();
            $world->setBlock($this->getPosition(), $this);
            BlockUpdateHelper::updateAroundDirectionRedstone($this, Facing::opposite($this->getFacing()));
            $world->addSound($this->getPosition()->add(0.5, 0.5, 0.5), new RedstonePowerOnSound());
            $world->scheduleDelayedBlockUpdate($this->getPosition(), 1);

            $block->setPowered(true);
            $world->setBlock($block->getPosition(), $block);
            BlockUpdateHelper::updateAroundDirectionRedstone($block, Facing::opposite($block->getFacing()));
            $world->addSound($block->getPosition()->add(0.5, 0.5, 0.5), new RedstonePowerOnSound());
            $world->scheduleDelayedBlockUpdate($block->getPosition(), 1);
            break;
        }
    }

    public function getStrongPower(int $face): int {
        return $this->isPowered() && $face == $this->getFacing() ? 15 : 0;
    }

    public function getWeakPower(int $face): int {
        return $this->isPowered() ? 15 : 0;
    }

    public function isPowerSource(): bool {
        return $this->isPowered();
    }
}