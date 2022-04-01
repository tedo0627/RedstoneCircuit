<?php

namespace tedo0627\redstonecircuit\block\mechanism;

use InvalidArgumentException;
use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\Opaque;
use pocketmine\block\utils\AnyFacingTrait;
use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\block\utils\PoweredByRedstoneTrait;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\projectile\Egg;
use pocketmine\entity\projectile\ExperienceBottle;
use pocketmine\entity\projectile\Snowball;
use pocketmine\entity\projectile\SplashPotion;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\ClickSound;
use tedo0627\redstonecircuit\block\BlockEntityInitializeTrait;
use tedo0627\redstonecircuit\block\BlockPowerHelper;
use tedo0627\redstonecircuit\block\dispenser\ArmorDispenseBehavior;
use tedo0627\redstonecircuit\block\dispenser\BoneMealDispenseBehavior;
use tedo0627\redstonecircuit\block\dispenser\BucketDispenseBehavior;
use tedo0627\redstonecircuit\block\dispenser\DefaultItemDispenseBehavior;
use tedo0627\redstonecircuit\block\dispenser\DispenseItemBehavior;
use tedo0627\redstonecircuit\block\dispenser\FlintSteelDispenseBehavior;
use tedo0627\redstonecircuit\block\dispenser\GlassBottleDispenseBehavior;
use tedo0627\redstonecircuit\block\dispenser\ProjectileDispenseBehavior;
use tedo0627\redstonecircuit\block\dispenser\ShulkerBoxDispenseBehavior;
use tedo0627\redstonecircuit\block\dispenser\TNTDispenseBehavior;
use tedo0627\redstonecircuit\block\entity\BlockEntityDispenser;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\event\BlockDispenseEvent;
use tedo0627\redstonecircuit\event\BlockRedstonePowerUpdateEvent;
use tedo0627\redstonecircuit\RedstoneCircuit;
use tedo0627\redstonecircuit\sound\ClickFailSound;

class BlockDispenser extends Opaque implements IRedstoneComponent {
    use AnyFacingTrait;
    use BlockEntityInitializeTrait;
    use PoweredByRedstoneTrait;
    use RedstoneComponentTrait;

    protected static bool $init = false;
    protected static DispenseItemBehavior $default;
    /** @var DispenseItemBehavior[] */
    protected static array $behaviors = [];

    public function __construct(BlockIdentifier $idInfo, string $name, BlockBreakInfo $breakInfo) {
        parent::__construct($idInfo, $name, $breakInfo);
        self::registerBehavior();
    }

    protected function writeStateToMeta(): int {
        return BlockDataSerializer::writeFacing($this->facing) |
            ($this->isPowered() ? 0x08 : 0);
    }

    public function readStateFromData(int $id, int $stateMeta): void {
        $this->setFacing(BlockDataSerializer::readFacing($stateMeta & 0x07));
        $this->setPowered(($stateMeta & 0x08) !== 0);
    }

    public function readStateFromWorld(): void {
        parent::readStateFromWorld();
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        if($tile instanceof BlockEntityDispenser) {
            $this->setInitialized($tile->isInitialized());
        }
    }

    public function writeStateToWorld(): void {
        parent::writeStateToWorld();
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        assert($tile instanceof BlockEntityDispenser);
        $tile->setInitialized($this->isInitialized());
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
        if (!$tile instanceof BlockEntityDispenser) return true;

        $inventory = $tile->getInventory();
        $player->setCurrentWindow($inventory);
        return true;
    }

    public function asItem(): Item {
        return ItemFactory::getInstance()->get($this->idInfo->getItemId(), 3);
    }

    public function onScheduledUpdate(): void {
        if (!$this->isInitialized()) {
            $this->setInitialized(true);
            $this->writeStateToWorld();
            return;
        }

        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        if (!$tile instanceof BlockEntityDispenser) return;

        $inventory = $tile->getInventory();
        $slot = $inventory->getRandomSlot();
        if ($slot === -1) {
            $this->getPosition()->getWorld()->addSound($this->getPosition(), new ClickFailSound(1.2));
            return;
        }

        $item = $inventory->getItem($slot);
        if (RedstoneCircuit::isCallEvent()) {
            $event = new BlockDispenseEvent($this, clone $item);
            $event->call();
            if ($event->isCancelled()) return;
        }

        $result = $this->dispense($item);
        $inventory->setItem($slot, $item);
        if ($result !== null) $inventory->addItem($result);
        $this->getPosition()->getWorld()->addSound($this->getPosition(), new ClickSound());
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
        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
        if ($powered) $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 4);
    }

    public function dispense(Item $item): ?Item {
        $id = $item->getId();
        if (array_key_exists($id, self::$behaviors)) {
            $behavior = self::$behaviors[$id];
            return $behavior->dispense($this, $item);
        }
        return self::$default->dispense($this, $item);
    }

    private static function registerBehavior() {
        if (self::$init) return;

        self::$init = true;
        self::$default = new DefaultItemDispenseBehavior();

        self::$behaviors[ItemIds::ARROW] = new class extends ProjectileDispenseBehavior {
            public function getEntity(Location $location, Item $item): Entity {
                return new Arrow($location, null, false);
            }
        };
        self::$behaviors[ItemIds::EGG] = new class extends ProjectileDispenseBehavior {
            public function getEntity(Location $location, Item $item): Entity {
                return new Egg($location, null);
            }
        };
        self::$behaviors[ItemIds::SNOWBALL] = new class extends ProjectileDispenseBehavior {
            public function getEntity(Location $location, Item $item): Entity {
                return new Snowball($location, null);
            }
        };
        self::$behaviors[ItemIds::EXPERIENCE_BOTTLE] = new class extends ProjectileDispenseBehavior {
            public function getEntity(Location $location, Item $item): Entity {
                return new ExperienceBottle($location, null);
            }
        };
        self::$behaviors[ItemIds::SPLASH_POTION] = new class extends ProjectileDispenseBehavior {
            public function getEntity(Location $location, Item $item): Entity {
                if (!$item instanceof \pocketmine\item\SplashPotion) throw new InvalidArgumentException("item was not SplashPotion");
                return new SplashPotion($location, null, $item->getType());
            }
        };
        self::$behaviors[ItemIds::BUCKET] = new BucketDispenseBehavior();
        self::$behaviors[ItemIds::FLINT_STEEL] = new FlintSteelDispenseBehavior();
        self::$behaviors[ItemIds::DYE] = new BoneMealDispenseBehavior();
        self::$behaviors[ItemIds::TNT] = new TNTDispenseBehavior();
        self::$behaviors[ItemIds::UNDYED_SHULKER_BOX] = new ShulkerBoxDispenseBehavior();
        self::$behaviors[ItemIds::SHULKER_BOX] = new ShulkerBoxDispenseBehavior();
        self::$behaviors[ItemIds::GLASS_BOTTLE] = new GlassBottleDispenseBehavior();
        foreach ([
            ItemIds::LEATHER_HELMET => 0, ItemIds::LEATHER_CHESTPLATE => 1, ItemIds::LEATHER_LEGGINGS => 2, ItemIds::LEATHER_BOOTS => 3,
            ItemIds::CHAIN_HELMET => 0, ItemIds::CHAIN_CHESTPLATE => 1, ItemIds::CHAIN_LEGGINGS => 2, ItemIds::CHAIN_BOOTS => 3,
            ItemIds::IRON_HELMET => 0, ItemIds::IRON_CHESTPLATE => 1, ItemIds::IRON_LEGGINGS => 2, ItemIds::IRON_BOOTS => 3,
            ItemIds::DIAMOND_HELMET => 0, ItemIds::DIAMOND_CHESTPLATE => 1, ItemIds::DIAMOND_LEGGINGS => 2, ItemIds::DIAMOND_BOOTS => 3,
            ItemIds::GOLDEN_HELMET => 0, ItemIds::GOLDEN_CHESTPLATE => 1, ItemIds::GOLDEN_LEGGINGS => 2, ItemIds::GOLDEN_BOOTS => 3,
            ItemIds::CARVED_PUMPKIN => 0, ItemIds::SKULL => 0, ItemIds::ELYTRA => 2, ItemIds::TURTLE_HELMET => 0,
            748 => 0, 749 => 1, 750 => 2, 751 => 3,
                 ] as $id => $slot) {
            self::$behaviors[$id] = new ArmorDispenseBehavior($slot);
        }
    }
}