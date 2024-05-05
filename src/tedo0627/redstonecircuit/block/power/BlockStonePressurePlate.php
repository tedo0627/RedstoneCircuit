<?php

namespace tedo0627\redstonecircuit\block\power;

use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\StonePressurePlate;
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
use tedo0627\redstonecircuit\event\BlockRedstonePowerUpdateEvent;
use tedo0627\redstonecircuit\RedstoneCircuit;

class BlockStonePressurePlate extends StonePressurePlate implements IRedstoneComponent, ILinkRedstoneWire {
    use LinkRedstoneWireTrait;
    use RedstoneComponentTrait;

    private readonly int $deactivationDelayTicks;

    public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo, int $deactivationDelayTicks = 20) {
        parent::__construct($idInfo, $name, $typeInfo, $deactivationDelayTicks);
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

        if ($entityCount > 0 || $this->hasOutputSignal()) {
            [$newState, $pressedChange] = $this->calculatePlateState($activatingEntities);

            if (PressurePlateUpdateEvent::hasHandlers()) {
                $ev = new PressurePlateUpdateEvent($this, $newState, $activatingEntities);
                $ev->call();
                $newState = $ev->isCancelled() ? null : $ev->getNewState();
            }

            // Add custom event
            $oldPressed = $this->isPressed();
            if ($newState instanceof self && $newState->isPressed() !== $oldPressed && RedstoneCircuit::isCallEvent()) {
                $event = new BlockRedstonePowerUpdateEvent($this, $newState->isPressed(), $oldPressed);
                $event->call();
                $newPressed = $event->getNewPowered();
                if ($oldPressed === $newPressed) $newState = null;
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
            if ($pressedChange ?? $this->hasOutputSignal()) {
                $world->scheduleDelayedBlockUpdate($this->getPosition(), $this->deactivationDelayTicks);
            }
        }
    }

    public function getStrongPower(int $face): int {
        return $this->isPressed() && $face === Facing::UP ? 15 : 0;
    }

    public function getWeakPower(int $face): int {
        return $this->isPressed() ? 15 : 0;
    }

    public function isPowerSource(): bool {
        return $this->isPressed();
    }
}