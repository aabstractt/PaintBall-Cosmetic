<?php


namespace paintball;


use pocketmine\block\Block;
use pocketmine\level\Position;

class BlockData {

    /** @var array */
    protected $storage = [];

    /**
     * @param string $name
     * @return BlockStorage
     */
    public function add(string $name): BlockStorage {
        $storage = new BlockStorage($name);
        $this->storage[strtolower($name)] = $storage;
        return $storage;
    }

    /**
     * @param string $name
     * @return BlockStorage
     */
    public function get(string $name): ?BlockStorage {
        if(!isset($this->storage[strtolower($name)]))  return null;
        return $this->storage[strtolower($name)];
    }

    /**
     * @param Position $pos
     * @return BlockStorage|null
     */
    public function getByPosition(Position $pos): ?BlockStorage {
        foreach($this->storage as $storage){
            if($storage->exists($pos)) return $storage;
        }
        return null;
    }

    /**
     * @param string $name
     */
    public function remove(string $name): void {
        if(!isset($this->storage[strtolower($name)]))  return;
        $storage = $this->get($name);
        $storage->restoreAll();
        if($storage->restore !== null) $storage->restore->getHandler()->cancel();
        unset($this->storage[strtolower($name)]);
    }

    /**
     * @param Position $pos
     * @return bool
     */
    public function exists(Position $pos): bool {
        foreach($this->storage as $storage){
            if($storage->exists($pos)) return true;
        }
        return false;
    }

    /**
     * @param Position $pos
     * @param int $radius
     * @param bool $hollow
     * @return array
     */
    public function getBlocksInRadius(Position $pos, int $radius, bool $hollow = false): array {
        $blocks = [];
        $bx = $pos->getX();
        $by = $pos->getY();
        $bz = $pos->getZ();
        for($x = ($bx - $radius); $x <= ($bx + $radius); $x++){
            for($y = ($by - $radius); $y <= ($by + $radius); $y++){
                for($z = ($bz - $radius); $z <= ($bz + $radius); $z++){
                    $distance = (($bx - $x) * ($bx - $x) + ($by - $y) * ($by - $y) + ($bz - $z) * ($bz - $z));
                    if($distance < $radius * $radius && !($hollow && $distance < (($radius - 1) * ($radius - 1)))){
                        $position = new Position($x, $pos->getY(), $z, $pos->getLevel());
                        if($position->getLevel()->getBlock($position)->getId() !== Block::SIGN_POST) $blocks[] = $position->getLevel()->getBlock($position);
                    }
                }
            }
        }
        return $blocks;
    }
}