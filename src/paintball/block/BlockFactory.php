<?php

declare(strict_types=1);

namespace paintball\block;

use pocketmine\block\Block;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

class BlockFactory {

    /** @var array<string, BlockStorage> */
    private static $storage = [];

    /**
     * @param string $name
     * @return BlockStorage
     */
    public static function addStorage(string $name): BlockStorage {
        return self::$storage[strtolower($name)] = new BlockStorage($name);
    }

    /**
     * @param string $name
     * @return BlockStorage|null
     */
    public static function getStorage(string $name): ?BlockStorage {
        return self::$storage[strtolower($name)] ?? null;
    }

    /**
     * @param Vector3 $pos
     * @return BlockStorage|null
     */
    public static function getStorageByPosition(Vector3 $pos): ?BlockStorage {
        foreach (self::$storage as $storage) {
            if (empty($storage->getBlock($pos))) continue;

            return $storage;
        }

        return null;
    }

    /**
     * @param string $name
     */
    public static function removeStorage(string $name): void {
        $storage = self::getStorage($name);

        if ($storage == null) return;

        $storage->updateRestoreBlocks(true);

        unset(self::$storage[strtolower($name)]);
    }

    /**
     * @return BlockStorage[]
     */
    public static function getStorages(): array {
        return self::$storage;
    }

    /**
     * @param Position $pos
     * @param int $radius
     * @param bool $hollow
     * @return Block[]
     */
    public static function getBlocksInRadius(Position $pos, int $radius, bool $hollow = false): array {
        $blocks = [];
        $bx = $pos->getX();

        $by = $pos->getY();

        $bz = $pos->getZ();

        for ($x = ($bx - $radius); $x <= ($bx + $radius); $x++) {
            for ($y = ($by - $radius); $y <= ($by + $radius); $y++) {
                for ($z = ($bz - $radius); $z <= ($bz + $radius); $z++) {
                    $distance = (($bx - $x) * ($bx - $x) + ($by - $y) * ($by - $y) + ($bz - $z) * ($bz - $z));

                    if ($distance < $radius * $radius && !($hollow && $distance < (($radius - 1) * ($radius - 1)))) {
                        $block = $pos->getLevelNonNull()->getBlockAt((int)$x, (int)$y, (int)$z);

                        if ($block->getId() == Block::SIGN_POST) continue;

                        $blocks[] = $block;
                    }
                }
            }
        }

        return $blocks;
    }

}