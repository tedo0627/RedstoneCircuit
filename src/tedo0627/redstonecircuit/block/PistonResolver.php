<?php

namespace tedo0627\redstonecircuit\block;

use pocketmine\block\BaseSign;
use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds as Ids;
use pocketmine\block\Door;
use pocketmine\block\Flowable;
use pocketmine\block\GlazedTerracotta;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\world\World;
use tedo0627\redstonecircuit\block\mechanism\BlockPiston;

class PistonResolver {

    private BlockPiston $piston;
    private bool $sticky;
    private bool $push;

    /** @var Block[] */
    private array $break = [];
    /** @var Block[] */
    private array $attach = [];

    /** @var int[] */
    private array $checked = [];

    private bool $success = false;

    public function __construct(BlockPiston $piston, bool $sticky, bool $push) {
        $this->piston = $piston;
        $this->sticky = $sticky;
        $this->push = $push;
    }

    public function resolve(): void {
        $face = $this->piston->getPistonArmFace();
        if ($this->push) {
            if ($this->calculateBlocks($this->piston->getSide($face), $face, $face)) $this->success = true;
        } else {
            if ($this->sticky) {
                $this->calculateBlocks($this->piston->getSide($face, 2), $face, Facing::opposite($face));
            }
            $this->success = true;
        }
        usort($this->attach, function(Block $a, Block $b) {
            $pos1 = $a->getPosition();
            $pos2 = $b->getPosition();
            $face = $this->piston->getPistonArmFace();
            $direction = ($this->push ? 1 : -1) * (Facing::isPositive($face) ? 1 : -1);
            return match (Facing::axis($face)) {
                Axis::Y => ($pos2->getFloorY() - $pos1->getFloorY()) * $direction,
                Axis::Z => ($pos2->getFloorZ() - $pos1->getFloorZ()) * $direction,
                Axis::X => ($pos2->getFloorX() - $pos1->getFloorX()) * $direction,
            };
        });
    }

    private function calculateBlocks(Block $block, int $face, int $breakFace): bool {
        $pos = $block->getPosition();
        $hash = World::chunkBlockHash($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
        if (in_array($hash, $this->checked, true)) return true;

        $this->checked[] = $hash;
        if ($block->getId() === Ids::AIR) return true;
        if (!$this->canMove($block)) {
            $result = $face !== $breakFace;
            if (!$result) {
                $this->break = [];
                $this->attach = [];
            }
            return $result;
        }

        if ($this->canBreak($block)) {
            if ($face === $breakFace) $this->break[] = $block;
            return true;
        }

        $y = $block->getPosition()->getSide($breakFace)->getY();
        if ($y < 0 || 255 < $y) {
            $this->break = [];
            $this->attach = [];
            return false;
        }

        if ($block instanceof GlazedTerracotta && $face !== $breakFace) return true;

        $this->attach[] = $block;
        if (count($this->attach) >= 13) {
            $this->break = [];
            $this->attach = [];
            return false;
        }

        if ($block->getId() === Ids::SLIME) {
            for ($i = 0; $i < 6; $i++) {
                if ($i === Facing::opposite($face)) continue;
                if (!$this->calculateBlocks($block->getSide($i), $i, $breakFace)) return false;
            }
        } else {
            if (!$this->calculateBlocks($block->getSide($breakFace), $breakFace, $breakFace)) return false;
        }
        return true;
    }

    /** @return Block[] */
    public function getAttachBlocks(): array {
        return $this->attach;
    }

    /** @return Block[] */
    public function getBreakBlocks(): array {
        return $this->break;
    }

    public function isSuccess(): bool {
        return $this->success;
    }

    private function canMove(Block $block): bool {
        if ($block instanceof BlockPiston) {
            if ($this->piston->getPosition()->equals($block->getPosition())) return false;
            return $block->getState() === 0;
        }
        $ids = [
            Ids::BEDROCK, Ids::PISTONARMCOLLISION, Ids::OBSIDIAN,
            Ids::MOB_SPAWNER, Ids::PORTAL, Ids::INVISIBLEBEDROCK,
            Ids::ENCHANTING_TABLE, Ids::END_PORTAL, Ids::END_PORTAL_FRAME,
            Ids::ENDER_CHEST, Ids::COMMAND_BLOCK, Ids::BEACON,
            Ids::REPEATING_COMMAND_BLOCK, Ids::CHAIN_COMMAND_BLOCK, Ids::END_GATEWAY,
            Ids::MOVINGBLOCK, Ids::STRUCTURE_BLOCK, Ids::BARRIER,
            Ids::JIGSAW, 472
        ];
        return !in_array($block->getId(), $ids, true);
    }

    private function canBreak(Block $block): bool {
        if ($block instanceof Flowable) return true;
        if ($block instanceof Door) return true;
        if ($block instanceof BaseSign) return true;

        $ids = [
            Ids::FLOWING_WATER, Ids::STILL_WATER, Ids::FLOWING_LAVA,
            Ids::STILL_LAVA, Ids::LEAVES, Ids::BED_BLOCK,
            Ids::LADDER, Ids::STONE_PRESSURE_PLATE, Ids::WOODEN_PRESSURE_PLATE,
            Ids::PUMPKIN, Ids::JACK_O_LANTERN, Ids::MELON_BLOCK,
            Ids::DRAGON_EGG, Ids::LIGHT_WEIGHTED_PRESSURE_PLATE, Ids::HEAVY_WEIGHTED_PRESSURE_PLATE,
            Ids::LEAVES2, Ids::STANDING_BANNER, Ids::WALL_BANNER,
            Ids::UNDYED_SHULKER_BOX, Ids::SHULKER_BOX, Ids::CARVED_PUMPKIN,
            Ids::CAMPFIRE
        ];
        return in_array($block->getId(), $ids, true);
    }
}