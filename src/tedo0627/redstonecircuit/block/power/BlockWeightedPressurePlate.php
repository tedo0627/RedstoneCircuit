<?php

namespace tedo0627\redstonecircuit\block\power;

use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\WeightedPressurePlate;
use pocketmine\entity\Entity;
use pocketmine\event\block\PressurePlateUpdateEvent;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\player\Player;
use pocketmine\world\sound\PressurePlateActivateSound;
use pocketmine\world\sound\PressurePlateDeactivateSound;
use tedo0627\redstonecircuit\block\BlockUpdateHelper;
use tedo0627\redstonecircuit\block\ILinkRedstoneWire;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\LinkRedstoneWireTrait;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\block\ScheduleUpdateHelper;
use tedo0627\redstonecircuit\CustomConfig;
use tedo0627\redstonecircuit\event\BlockRedstoneSignalUpdateEvent;

class BlockWeightedPressurePlate extends WeightedPressurePlate implements IRedstoneComponent, ILinkRedstoneWire {
    use LinkRedstoneWireTrait;
    use RedstoneComponentTrait;

    private readonly int $deactivationDelayTicks;

    public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo, int $deactivationDelayTicks = 10, float $signalStrengthFactor = 1.0) {
        parent::__construct($idInfo, $name, $typeInfo, $deactivationDelayTicks, $signalStrengthFactor);
        $this->deactivationDelayTicks = $deactivationDelayTicks;
    }

    public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []): bool {
        parent::onBreak($item, $player, $returnedItems);
        BlockUpdateHelper::updateAroundDirectionRedstone($this, Facing::DOWN);
        return true;
    }

    protected function filterIrrelevantEntities(array $entities): array {
        $entities = parent::filterIrrelevantEntities($entities);
        return array_filter($entities, fn(Entity $e) => !($e instanceof Player && $e->isSpectator()));
    }

    public function onScheduledUpdate(): void {
        $world = $this->getPosition()->getWorld();

        $intersectionAABB = $this->getActivationBox();
        $activatingEntities = $this->filterIrrelevantEntities($world->getNearbyEntities($intersectionAABB));
        $entityCount = count($activatingEntities);

        // Fix pressure plate delay
        if ($entityCount > 0 && CustomConfig::isFixScheduleUpdate()) {
            ScheduleUpdateHelper::scheduleUpdate($this->getPosition(), $this->deactivationDelayTicks);
        }

        if ($entityCount > 0 || $this->hasOutputSignal()) {
            [$newState, $pressedChange] = $this->calculatePlateState($activatingEntities);

            if (PressurePlateUpdateEvent::hasHandlers()) {
                $ev = new PressurePlateUpdateEvent($this, $newState, $activatingEntities);
                $ev->call();
                $newState = $ev->isCancelled() ? null : $ev->getNewState();
            }

            // Add custom event
            $oldSignal = $this->getOutputSignalStrength();
            if ($newState instanceof self && $newState->getOutputSignalStrength() !== $oldSignal && CustomConfig::isCallEvent()) {
                $event = new BlockRedstoneSignalUpdateEvent($this, $newState->getOutputSignalStrength(), $oldSignal);
                $event->call();
                $newSignal = $event->getNewSignal();
                if ($oldSignal === $newSignal) $newState = null;
            }

            if ($newState !== null) {
                $world->setBlock($this->getPosition(), $newState);
                // Update around redstone
                BlockUpdateHelper::updateAroundDirectionRedstone($this, Facing::DOWN);

                if ($pressedChange !== null) {
                    $world->addSound($this->getPosition(), $pressedChange ?
                        new PressurePlateActivateSound($this) :
                        new PressurePlateDeactivateSound($this)
                    );
                }
            }
            if ($pressedChange ?? $this->hasOutputSignal() && !CustomConfig::isFixScheduleUpdate()) {
                $world->scheduleDelayedBlockUpdate($this->getPosition(), $this->deactivationDelayTicks);
            }
        }
    }

    public function onEntityInside(Entity $entity): bool {
        if ($entity instanceof Player && $entity->isSpectator()) return true;

        if (CustomConfig::isFixScheduleUpdate()) {
            $this->onScheduledUpdate();
        } else {
            parent::onEntityInside($entity);
        }
        return true;
    }

    public function getStrongPower(int $face): int {
        return $face === Facing::UP ? $this->getOutputSignalStrength() : 0;
    }

    public function getWeakPower(int $face): int {
        return $this->getOutputSignalStrength();
    }

    public function isPowerSource(): bool {
        return $this->getOutputSignalStrength() > 0;
    }
}