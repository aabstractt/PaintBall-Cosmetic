<?php

namespace paintball;

use pocketmine\block\Block;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;

class BlockStorage {

    /** @var string */
    protected $name;

    /** @var array */
    protected $backup = [];

    /** @var Restore */
    public $restore = null;

    /**
     * BlockStorage constructor.
     * @param string $name
     */
    public function __construct(string $name){
        $this->name = $name;
    }

    /**
     * @return Player
     */
    public function getPlayer(): ?Player{
        return Server::getInstance()->getPlayer($this->name);
    }

    /**
     * @param Block $block
     */
    public function add(Block $block){
        $pos = $block->asPosition();
        if(Main::getInstance()->getBlockData()->exists($pos)){
            $data = Main::getInstance()->getBlockData()->getByPosition($pos)->get($pos);
            $data['time'] = time();
            Main::getInstance()->getBlockData()->getByPosition($pos)->set($pos, $data);
            return;
        }
        $this->backup[$pos->getX() . ':' . $pos->getY() . ':' . $pos->getZ()] = ['id' => $block->getId(), 'damage' => $block->getDamage(), 'time' => time()];
    }

    /**
     * @param Position $pos
     * @return bool
     */
    public function exists(Position $pos): bool {
        return isset($this->backup[$pos->getX() . ':' . $pos->getY() . ':' . $pos->getZ()]);
    }

    /**
     * @param Position $pos
     */
    public function restore(Position $pos): void {
        if(!$this->exists($pos)) return;
        $data = $this->backup[$pos->getX() . ':' . $pos->getY() . ':' . $pos->getZ()];
        $pos->getLevel()->setBlock($pos, Block::get($data['id'], $data['damage']));
        unset($this->backup[$pos->getX() . ':' . $pos->getY() . ':' . $pos->getZ()]);
    }

    public function restoreAll(): void {
        foreach($this->getAll() as $data){
            $this->restore($data['pos']);
        }
    }

    /**
     * @return array
     */
    public function getAll(): array {
        $data = [];
        foreach($this->backup as $pos => $value){
            $pos2 = $pos;
            $pos = explode(':', $pos);
            $backup = [
                'pos' => new Position($pos[0], $pos[1], $pos[2], Server::getInstance()->getDefaultLevel()),
                'id' => $value['id'],
                'damage' => $value['damage'],
                'time' => $value['time']
            ];
            $data[$pos2] = $backup;
        }
        return $data;
    }

    /**
     * @param Position $pos
     * @return array
     */
    public function get(Position $pos): array {
        return $this->backup[$pos->getX() . ':' . $pos->getY() . ':' . $pos->getZ()];
    }

    /**
     * @param Position $pos
     * @param array $data
     */
    public function set(Position $pos, array $data){
        $this->backup[$pos->getX() . ':' . $pos->getY() . ':' . $pos->getZ()] = $data;
    }
}