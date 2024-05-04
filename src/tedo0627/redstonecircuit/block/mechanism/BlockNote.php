<?php

namespace tedo0627\redstonecircuit\block\mechanism;

use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds as Ids;
use pocketmine\block\Note;
use pocketmine\block\utils\PoweredByRedstoneTrait;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\NoteInstrument;
use pocketmine\world\sound\NoteSound;
use tedo0627\redstonecircuit\block\BlockPowerHelper;
use tedo0627\redstonecircuit\block\entity\BlockEntityNote;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\event\BlockRedstonePowerUpdateEvent;
use tedo0627\redstonecircuit\RedstoneCircuit;

class BlockNote extends Note implements IRedstoneComponent {
    use PoweredByRedstoneTrait;
    use RedstoneComponentTrait;

    public function readStateFromWorld(): void {
        parent::readStateFromWorld();
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        if($tile instanceof BlockEntityNote) $this->setPowered($tile->isPowered());
    }

    public function writeStateToWorld(): void {
        parent::writeStateToWorld();
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        assert($tile instanceof BlockEntityNote);
        $tile->setPowered($this->isPowered());
    }

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool {
        $this->setPowered(BlockPowerHelper::isPowered($blockReplace));
        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null): bool {
        $pitch = $this->getPitch() + 1;
        if ($pitch > Note::MAX_PITCH) $pitch = Note::MIN_PITCH;
        $this->setPitch($pitch);
        $this->playSound();
        return true;
    }

    public function onAttack(Item $item, int $face, ?Player $player = null): bool {
        $this->playSound();
        return false;
    }

    public function playSound() {
        $pos = $this->getPosition();
        $world = $pos->getWorld();
        $world->addSound($pos, new NoteSound($this->getSound(), $this->getPitch()));
        $world->broadcastPacketToViewers($pos, BlockEventPacket::create(BlockPosition::fromVector3($pos), 1, $this->getPitch()));
    }

    public function getSound(): NoteInstrument {
        return match ($this->getSide(Facing::DOWN)->getId()) {
            Ids::STONE, Ids::COBBLESTONE, Ids::GOLD_ORE, Ids::IRON_ORE, Ids::COAL_ORE,
            Ids::LAPIS_ORE, Ids::BRICK_BLOCK, Ids::MOSSY_COBBLESTONE, Ids::OBSIDIAN,
            Ids::DIAMOND_ORE, Ids::COBBLESTONE_STAIRS, Ids::REDSTONE_ORE, Ids::LIT_REDSTONE_ORE,
            Ids::NETHERRACK, Ids::MONSTER_EGG, Ids::BRICK_STAIRS, Ids::STONE_BRICK_STAIRS,
            Ids::NETHER_QUARTZ_ORE // TODO
            => NoteInstrument::BASS_DRUM(),

            Ids::SAND, Ids::GRAVEL, Ids::SOUL_SAND, Ids::CONCRETEPOWDER
            => NoteInstrument::SNARE(),

            Ids::GLASS, Ids::GLASS_PANE, Ids::STAINED_GLASS_PANE, Ids::STAINED_GLASS
            => NoteInstrument::CLICKS_AND_STICKS(),

            Ids::PLANKS, Ids::LOG, Ids::BOOKSHELF, Ids::OAK_STAIRS, Ids::CRAFTING_TABLE,
            Ids::SPRUCE_STAIRS, Ids::BIRCH_STAIRS, Ids::JUNGLE_STAIRS, Ids::DOUBLE_WOODEN_SLAB,
            Ids::LOG2 // TODO
            => NoteInstrument::DOUBLE_BASS(),

            default => NoteInstrument::PIANO()
        };
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
        $this->writeStateToWorld();
        if ($powered) $this->playSound();
    }
}