<?php

namespace tedo0627\redstonecircuit\block;

use pocketmine\block\utils\PoweredByRedstoneTrait;

trait CommandBlockTrait {
    use PoweredByRedstoneTrait;

    protected int $commandBlockMode = 0;
    protected string $command = "";
    protected string $lastOutput = "";
    protected bool $auto = false;
    protected bool $conditionalMode = false;
    protected int $tickDelay = 0;
    protected bool $executeOnFirstTick = false;

    protected int $successCount = 0;
    protected int $tick = -1;

    public function getCommandBlockMode(): int {
        return $this->commandBlockMode;
    }

    public function setCommandBlockMode(int $mode): void {
        $this->commandBlockMode = $mode;
    }

    public function getCommand(): string {
        return $this->command;
    }

    public function setCommand(string $command): void {
        $this->command = $command;
    }

    public function getLastOutput(): string {
        return $this->lastOutput;
    }

    public function setLastOutput(string $output): void {
        $this->lastOutput = $output;
    }

    public function isAuto(): bool {
        return $this->auto;
    }

    public function setAuto(bool $auto): void {
        $this->auto = $auto;
    }

    public function isConditionalMode(): bool {
        return $this->conditionalMode;
    }

    public function setConditionalMode(bool $conditionalMode): void {
        $this->conditionalMode = $conditionalMode;
    }

    public function getTickDelay(): int {
        return $this->tickDelay;
    }

    public function setTickDelay(int $delay): void {
        $this->tickDelay = $delay;
    }

    public function isExecuteOnFirstTick(): bool {
        return $this->executeOnFirstTick;
    }

    public function setExecuteOnFirstTick(bool $executeOnFirstTick): void {
        $this->executeOnFirstTick = $executeOnFirstTick;
    }

    public function getSuccessCount(): int {
        return $this->successCount;
    }

    public function setSuccessCount(int $count): void {
        $this->successCount = $count;
    }

    public function getTick(): int {
        return $this->tick;
    }

    public function setTick(int $tick): void {
        $this->tick = $tick;
    }
}