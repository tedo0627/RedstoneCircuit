<?php

namespace tedo0627\redstonecircuit\block\power;

use pocketmine\block\DaylightSensor;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use tedo0627\redstonecircuit\block\BlockUpdateHelper;
use tedo0627\redstonecircuit\block\ILinkRedstoneWire;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\LinkRedstoneWireTrait;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\event\BlockRedstoneSignalUpdateEvent;
use tedo0627\redstonecircuit\RedstoneCircuit;

class BlockDaylightSensor extends DaylightSensor implements IRedstoneComponent, ILinkRedstoneWire {
    use LinkRedstoneWireTrait;
    use RedstoneComponentTrait;

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null): bool {
        $this->setInverted(!$this->isInverted());
        $this->updateSignal();
        return true;
    }

    public function onScheduledUpdate(): void {
        $this->updateSignal();
        $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 20);
    }

    public function getWeakPower(int $face): int {
        return $this->getOutputSignalStrength();
    }

    public function isPowerSource(): bool {
        return $this->getOutputSignalStrength() > 0;
    }

    protected function updateSignal(): void {
        $oldSignal = $this->getOutputSignalStrength();
        $signal = $this->recalculateSignalStrength();
        if ($oldSignal === $signal) return;

        if (RedstoneCircuit::isCallEvent()) {
            $event = new BlockRedstoneSignalUpdateEvent($this, $signal, $oldSignal);
            $event->call();
            $signal = $event->getNewSignal();
            if ($oldSignal === $signal) return;
        }

        $this->setOutputSignalStrength($signal);
        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
        BlockUpdateHelper::updateAroundRedstone($this);
    }

    private function recalculateSignalStrength(): int {
        $pos = $this->getPosition();
        $lightLevel = $pos->getWorld()->getRealBlockSkyLightAt($pos->getX(), $pos->getY(), $pos->getZ());
        if($this->isInverted()) return 15 - $lightLevel;

        $sunAngle = $pos->getWorld()->getSunAnglePercentage();
        return max(0, (int) round($lightLevel * cos(($sunAngle + ((($sunAngle < 0.5 ? 0 : 1) - $sunAngle) / 5)) * 2 * M_PI)));
    }
}