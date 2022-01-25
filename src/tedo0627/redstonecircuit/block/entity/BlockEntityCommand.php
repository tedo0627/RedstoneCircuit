<?php

namespace tedo0627\redstonecircuit\block\entity;

use pocketmine\block\tile\Container;
use pocketmine\block\tile\ContainerTrait;
use pocketmine\block\tile\Nameable;
use pocketmine\block\tile\NameableTrait;
use pocketmine\block\tile\Spawnable;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;
use tedo0627\redstonecircuit\block\CommandBlockTrait;
use tedo0627\redstonecircuit\block\inventory\CommandInventory;

class BlockEntityCommand extends Spawnable implements Container, Nameable {
    use NameableTrait {
        addAdditionalSpawnData as addNameSpawnData;
    }
    use ContainerTrait {
        onBlockDestroyedHook as containerTraitBlockDestroyedHook;
    }
    use CommandBlockTrait;

    protected CommandInventory $inventory;

    public function __construct(World $world, Vector3 $pos) {
        parent::__construct($world, $pos);
        $this->inventory = new CommandInventory($this->getPosition());
    }

    public function readSaveData(CompoundTag $nbt): void {
        $this->commandBlockMode = $nbt->getInt("commandBlockMode");
        $this->command = $nbt->getString("command");
        $this->lastOutput = $nbt->getString("lastOutput");
        $this->auto = $nbt->getByte("auto") === 1;
        $this->conditionalMode = $nbt->getByte("conditionalMode") === 1;
        $this->tickDelay = $nbt->getInt("tickDelay");
        $this->executeOnFirstTick = $nbt->getByte("executeOnFirstTick") === 1;
        $this->powered = $nbt->getByte("powered") === 1;

        $this->successCount = $nbt->getInt("successCount");
        $this->lastExecution = $nbt->getInt("lastExecution");

        $this->loadName($nbt);
    }

    protected function writeSaveData(CompoundTag $nbt): void {
        $nbt->setInt("commandBlockMode", $this->commandBlockMode);
        $nbt->setString("command", $this->command);
        $nbt->setString("lastOutput", $this->lastOutput);
        $nbt->setByte("auto", $this->auto ? 1 : 0);
        $nbt->setByte("conditionalMode", $this->conditionalMode ? 1 : 0);
        $nbt->setInt("tickDelay", $this->tickDelay);
        $nbt->setByte("executeOnFirstTick", $this->executeOnFirstTick ? 1 : 0);
        $nbt->setByte("powered", $this->powered ? 1 : 0);

        $nbt->setInt("successCount", $this->successCount);
        $nbt->setInt("lastExecution", $this->lastExecution);

        $this->saveName($nbt);
    }

    /**
     * @return CommandInventory
     */
    public function getInventory() {
        return $this->inventory;
    }

    /**
     * @return CommandInventory
     */
    public function getRealInventory() {
        return $this->inventory;
    }

    public function getDefaultName(): string {
        return "CommandBlock";
    }

    public function addAdditionalSpawnData(CompoundTag $nbt): void {
        $nbt->setInt("commandBlockMode", $this->commandBlockMode);
        $nbt->setString("Command", $this->command);
        $nbt->setString("LastOutput", $this->lastOutput);
        $nbt->setByte("auto", $this->auto ? 1 : 0);
        $nbt->setByte("conditionalMode", $this->conditionalMode ? 1 : 0);
        $nbt->setInt("tickDelay", $this->tickDelay);
        $nbt->setByte("executeOnFirstTick", $this->executeOnFirstTick ? 1 : 0);
        $nbt->setByte("powered", $this->powered ? 1 : 0);

        $this->addNameSpawnData($nbt);
    }
}