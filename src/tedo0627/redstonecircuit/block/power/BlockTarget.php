<?php

namespace tedo0627\redstonecircuit\block\power;

use pocketmine\block\Opaque;
use pocketmine\block\utils\AnalogRedstoneSignalEmitterTrait;
use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\item\Item;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use tedo0627\redstonecircuit\block\BlockUpdateHelper;
use tedo0627\redstonecircuit\block\entity\BlockEntityTarget;
use tedo0627\redstonecircuit\block\ILinkRedstoneWire;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\LinkRedstoneWireTrait;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\event\BlockRedstoneSignalUpdateEvent;
use tedo0627\redstonecircuit\RedstoneCircuit;

class BlockTarget extends Opaque implements IRedstoneComponent, ILinkRedstoneWire {
    use AnalogRedstoneSignalEmitterTrait;
    use RedstoneComponentTrait;
    use LinkRedstoneWireTrait;

    public function readStateFromWorld(): void {
        parent::readStateFromWorld();
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        if (!$tile instanceof BlockEntityTarget) return;

        $this->setOutputSignalStrength($tile->getOutputSignalStrength());
    }

    public function writeStateToWorld(): void {
        parent::writeStateToWorld();
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        assert($tile instanceof BlockEntityTarget);

        $tile->setOutputSignalStrength($this->getOutputSignalStrength());
    }

    public function onBreak(Item $item, ?Player $player = null): bool {
        parent::onBreak($item, $player);
        BlockUpdateHelper::updateAroundRedstone($this);
        return true;
    }

    public function onScheduledUpdate(): void {
        if ($this->getOutputSignalStrength() === 0) return;

        $signal = 0;
        if (RedstoneCircuit::isCallEvent()) {
            $event = new BlockRedstoneSignalUpdateEvent($this, $signal, $this->getOutputSignalStrength());
            $event->call();
            $signal = $event->getNewSignal();
        }
        $this->setOutputSignalStrength($signal);
        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
        BlockUpdateHelper::updateAroundRedstone($this);
    }

    public function getWeakPower(int $face): int {
        return $this->getOutputSignalStrength();
    }

    public function isPowerSource(): bool {
        return $this->getOutputSignalStrength() > 0;
    }

    public function hit(Entity $entity, int $face, Vector3 $pos): void {
        $x = abs($this->frac($pos->getX()) - 0.5);
        $y = abs($this->frac($pos->getY()) - 0.5);
        $z = abs($this->frac($pos->getZ()) - 0.5);
        $max = match (Facing::axis($face)) {
            Axis::X => max($y, $z),
            Axis::Y => max($x, $z),
            Axis::Z => max($x, $y)
        };
        $signal = (int) max(1, ceil(15 * $this->clamp((0.5 - $max) / 0.5, 0.0, 1.0)));
        $oldSignal = $this->getOutputSignalStrength();
        if ($oldSignal !== $signal && RedstoneCircuit::isCallEvent()) {
            $event = new BlockRedstoneSignalUpdateEvent($this, $signal, $oldSignal);
            $event->call();
            $signal = $event->getNewSignal();
        }
        $this->setOutputSignalStrength($signal);
        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
        BlockUpdateHelper::updateAroundRedstone($this);
        $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), $entity instanceof Arrow ? 20 : 8);
    }

    private function frac(float $value): float {
        $i = (int) $value;
        return $value - ($value < $i ? $i - 1 : $i);
    }

    private function clamp(float $value, float $min, float $max): float {
        if ($value < $min) return $min;
        return min($value, $max);
    }
}