<?php

namespace tedo0627\redstonecircuit\block\mechanism;

use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifierFlattened;
use pocketmine\block\Opaque;
use pocketmine\block\utils\AnyFacingTrait;
use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\block\utils\PoweredByRedstoneTrait;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\event\server\CommandEvent;
use pocketmine\item\Item;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\lang\Language;
use pocketmine\lang\Translatable;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\InventoryManager;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\PermissibleBase;
use pocketmine\permission\PermissibleDelegateTrait;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\BlockTransaction;
use pocketmine\world\World;
use tedo0627\redstonecircuit\block\BlockPowerHelper;
use tedo0627\redstonecircuit\block\CommandBlockTrait;
use tedo0627\redstonecircuit\block\entity\BlockEntityCommand;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\event\BlockRedstonePowerUpdateEvent;
use tedo0627\redstonecircuit\RedstoneCircuit;

class BlockCommand extends Opaque implements IRedstoneComponent, CommandSender {
    use AnyFacingTrait;
    use CommandBlockTrait;
    use PermissibleDelegateTrait;
    use PoweredByRedstoneTrait;
    use RedstoneComponentTrait;

    public const NORMAL = 0;
    public const REPEATING = 1;
    public const CHAIN = 2;

    protected BlockIdentifierFlattened $idInfoFlattened;

    protected string $customName = "";

    public function __construct(BlockIdentifierFlattened $idInfo, string $name, BlockBreakInfo $breakInfo) {
        $this->idInfoFlattened = $idInfo;
        $this->perm = new PermissibleBase([DefaultPermissions::ROOT_OPERATOR => true]);
        parent::__construct($idInfo, $name, $breakInfo);
    }

    public function getId(): int {
        return $this->idInfoFlattened->getAllBlockIds()[$this->commandBlockMode];
    }

    protected function writeStateToMeta(): int {
        return BlockDataSerializer::writeFacing($this->facing);
    }

    public function readStateFromData(int $id, int $stateMeta): void {
        $this->setFacing(BlockDataSerializer::readFacing($stateMeta & 0x07));
        $this->commandBlockMode = match ($id) {
            $this->idInfoFlattened->getBlockId() => 0,
            $this->idInfoFlattened->getAdditionalId(0) => 1,
            $this->idInfoFlattened->getAdditionalId(1) => 2
        };
    }

    public function readStateFromWorld(): void {
        parent::readStateFromWorld();
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        if(!$tile instanceof BlockEntityCommand) return;

        $this->setCommandBlockMode($tile->getCommandBlockMode());
        $this->setCommand($tile->getCommand());
        $this->setLastOutput($tile->getLastOutput());
        $this->setAuto($tile->isAuto());
        $this->setConditionalMode($tile->isConditionalMode());
        $this->setTickDelay($tile->getTickDelay());
        $this->setExecuteOnFirstTick($tile->isExecuteOnFirstTick());
        $this->setPowered($tile->isPowered());
        $this->setSuccessCount($tile->getSuccessCount());
        $this->setTick($tile->getTick());
        $this->setCustomName($tile->hasName() ? $tile->getName() : "");
    }

    public function writeStateToWorld(): void {
        parent::writeStateToWorld();
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        assert($tile instanceof BlockEntityCommand);

        $tile->setCommandBlockMode($this->getCommandBlockMode());
        $tile->setCommand($this->getCommand());
        $tile->setLastOutput($this->getLastOutput());
        $tile->setAuto($this->isAuto());
        $tile->setConditionalMode($this->isConditionalMode());
        $tile->setTickDelay($this->getTickDelay());
        $tile->setExecuteOnFirstTick($this->isExecuteOnFirstTick());
        $tile->setPowered($this->isPowered());
        $tile->setSuccessCount($this->getSuccessCount());
        $tile->setTick($this->getTick());
        $tile->setName($this->getCustomName());
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
                $this->setFacing(Facing::opposite($player->getHorizontalFacing()));
            }
        }
        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null): bool {
        if ($player === null) return false;

        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        if (!$tile instanceof BlockEntityCommand) return true;
        if (!$player->isCreative()) return true;
        if (!Server::getInstance()->isOp($player->getName())) return true;

        $inventoryManager = $player->getNetworkSession()->getInvManager();

        $reflection = new \ReflectionClass(InventoryManager::class);
        $property = $reflection->getProperty("lastInventoryNetworkId");
        $property->setAccessible(true);
        $value = $property->getValue($inventoryManager);
        $value = max(ContainerIds::FIRST, ($value + 1) % ContainerIds::LAST);
        $property->setValue($inventoryManager, $value);

        $pk = ContainerOpenPacket::blockInv($value, WindowTypes::COMMAND_BLOCK, BlockPosition::fromVector3($this->getPosition()));
        $player->getNetworkSession()->sendDataPacket($pk);
        return true;
    }

    public function onScheduledUpdate(): void {
        $tick = $this->getTick();
        $mode = $this->getCommandBlockMode();
        if ($tick > 0) {
            if ($mode === BlockCommand::REPEATING && !$this->check()) {
                $this->setTick(-1);
                $this->writeStateToWorld();
                $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 1);
                return;
            }

            $this->setTick($tick - 1);
            if ($tick === 1) {
                $this->execute();
                if ($mode === BlockCommand::REPEATING) $this->delay();
                return;
            }

            $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 1);
            return;
        }

        if ($mode !== BlockCommand::REPEATING) return;

        if ($this->getTickDelay() === 0 || ($tick === -1 && $this->isExecuteOnFirstTick())) {
            $this->setTick(0);
            $this->execute();
        }
        $this->delay();
    }

    public function onRedstoneUpdate(): void {
        $powered = BlockPowerHelper::isPowered($this);
        if ($powered === $this->isPowered()) return;

        if (RedstoneCircuit::isCallEvent()) {
            $event = new BlockRedstonePowerUpdateEvent($this, $powered, $this->isPowered());
            $event->call();
            $powered = $event->getNewPowered();
            if ($powered === $this->isPowered()) return;
        }

        $this->setPowered($powered);
        if ($powered) {
            $mode = $this->getCommandBlockMode();
            if ($mode === BlockCommand::REPEATING) $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 1);

            if ($mode !== BlockCommand::NORMAL) {
                $this->writeStateToWorld();
                return;
            }

            if ($this->getTickDelay() === 0) {
                $this->execute();
            } else {
                $this->delay();
            }
        } else {
            $this->writeStateToWorld();
        }
    }

    protected function delay(): void {
        $this->setTick($this->getTickDelay());
        $this->writeStateToWorld();
        $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 1);
    }

    protected function execute(): void {
        $successful = false;
        if ($this->check()) $successful = $this->dispatch();
        $this->setSuccessCount($successful ? 1 : 0);
        $this->writeStateToWorld();

        $block = $this->getSide($this->getFacing());
        if (!$block instanceof BlockCommand) return;
        if ($block->getCommandBlockMode() !== BlockCommand::CHAIN) return;

        $pos = $this->getPosition();
        $index = World::blockHash($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
        $block->chain([$index]);
    }

    protected function check(): bool {
        if ($this->getCommand() === "") return false;

        if ($this->isConditionalMode()) {
            $block = $this->getSide(Facing::opposite($this->getFacing()));
            if (!$block instanceof BlockCommand) return false;
            if ($block->getSuccessCount() <= 0) return false;
        }

        if ($this->isAuto()) return true;
        return BlockPowerHelper::isPowered($this);
    }

    protected function chain(array $blockIndex = []): void {
        $pos = $this->getPosition();
        $index = World::blockHash($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
        if (in_array($index, $blockIndex, true)) return;

        if ($this->getTickDelay() !== 0) {
            $this->delay();
            return;
        }

        $successful = false;
        if ($this->check()) $successful = $this->dispatch();
        $this->setSuccessCount($successful ? 1 : 0);
        $block = $this->getSide($this->getFacing());
        if (!$block instanceof BlockCommand) return;
        if ($block->getCommandBlockMode() !== BlockCommand::CHAIN) return;

        $pos = $this->getPosition();
        $blockIndex[] = World::blockHash($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
        $block->chain($blockIndex);
    }

    protected function dispatch(): bool {
        $command = $this->getCommand();
        if (RedstoneCircuit::isCallEvent()) {
            $event = new CommandEvent($this, $command);
            $event->call();
            if ($event->isCancelled()) return false;

            $command = $event->getCommand();
        }

        $args = [];
        preg_match_all('/"((?:\\\\.|[^\\\\"])*)"|(\S+)/u', $command, $matches);
        foreach($matches[0] as $k => $_){
            for($i = 1; $i <= 2; ++$i){
                if($matches[$i][$k] !== ""){
                    $args[$k] = $i === 1 ? stripslashes($matches[$i][$k]) : $matches[$i][$k];
                    break;
                }
            }
        }

        $successful = false;
        $sentCommandLabel = array_shift($args);
        if ($sentCommandLabel !== null && ($target = Server::getInstance()->getCommandMap()->getCommand($sentCommandLabel)) !== null) {
            $target->timings->startTiming();

            try {
                $result = $target->execute($this, $sentCommandLabel, $args);
                if (is_bool($result)) $successful = $result;
            } catch (InvalidCommandSyntaxException) {
                $this->sendMessage($this->getLanguage()->translate(KnownTranslationFactory::commands_generic_usage($target->getUsage())));
            } finally {
                $target->timings->stopTiming();
            }
        } else {
            $this->sendMessage(KnownTranslationFactory::pocketmine_command_notFound($sentCommandLabel ?? "", "/help")->prefix(TextFormat::RED));
        }
        return $successful;
    }

    public function getCustomName(): string {
        return $this->customName;
    }

    public function setCustomName(string $name): void {
        $this->customName = $name;
    }

    // interface CommandSender

    public function getLanguage(): Language {
        return $this->getServer()->getLanguage();
    }

    public function sendMessage(Translatable|string $message): void {
        if ($message instanceof Translatable) $message = $this->getLanguage()->translate($message);
        $this->setLastOutput($message);
    }

    public function getServer(): Server {
        return Server::getInstance();
    }

    public function getScreenLineHeight(): int {
        return 1;
    }

    public function setScreenLineHeight(?int $height): void {

    }
}