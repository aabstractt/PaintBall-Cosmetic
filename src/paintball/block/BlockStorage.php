<?php

declare(strict_types=1);

namespace paintball\block;

use paintball\Main;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

class BlockStorage {

    /** @var string */
    private $name;
    /** @var array */
    protected $backup = [];
    /** @var int */
    private $taskId = -1;

    /**
     * BlockStorage constructor.
     * @param string $name
     */
    public function __construct(string $name) {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return Player
     */
    public function getPlayer(): ?Player{
        return Server::getInstance()->getPlayer($this->name);
    }

    /**
     * @param Block $block
     * @noinspection PhpUnusedParameterInspection
     */
    public function addBlock(Block $block): void {
        $pos = $block->asPosition();

        $storage = BlockFactory::getStorageByPosition($pos);

        if ($storage == null || strtolower($storage->getName()) == strtolower($this->getName())) {
            $this->backup[$pos->getX() . ':' . $pos->getY() . ':' . $pos->getZ()] = ['id' => $block->getId(), 'damage' => $block->getDamage(), 'time' => time()];

            if ($this->taskId == -1) {
                $this->taskId = Main::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function(int $currentTick): void {
                    $this->updateRestoreBlocks($this->getPlayer() == null);
                }), 20)->getTaskId();
            }

            return;
        }

        $data = $storage->getBlock($pos);

        $data['time'] = time();

        $block = Block::get($data['id'], $data['damage']);

        $block->position($pos);

        $storage->addBlock($block);
    }

    /**
     * @param Vector3 $pos
     * @return array
     */
    public function getBlock(Vector3 $pos): array {
        return $this->backup[$pos->getX() . ':' . $pos->getY() . ':' . $pos->getZ()] ?? [];
    }

    /**
     * @param Vector3 $pos
     * @param array $data
     */
    public function updateData(Vector3 $pos, array $data): void {
        $this->backup[$pos->getX() . ':' . $pos->getY() . ':' . $pos->getZ()] = $data;
    }

    /**
     * @param Vector3 $pos
     * @return bool
     */
    public function isBlock(Vector3 $pos): bool {
        return !empty($this->getBlock($pos));
    }

    /**
     * @param Vector3 $pos
     */
    public function restoreBlock(Vector3 $pos): void {
        if (!$this->isBlock($pos)) return;

        $level = Server::getInstance()->getDefaultLevel();

        if ($level == null) return;

        $data = $this->getBlock($pos);

        $level->setBlock($pos, Block::get($data['id'], $data['damage']));

        unset($this->backup[$pos->getX() . ':' . $pos->getY() . ':' . $pos->getZ()]);
    }

    /**
     * @param bool $force
     */
    public function updateRestoreBlocks(bool $force = false): void {
        foreach ($this->backup as $k => $v) {

            if (!$force && (time() - $v['time']) < 5) continue;

            list($x, $y, $z) = explode(':', $k);

            $pos = new Vector3((int)$x, (int)$y, (int)$z);

            $this->restoreBlock($pos);
        }

        if (count($this->backup) <= 0) {
            Main::getInstance()->getScheduler()->cancelTask($this->taskId);

            $this->taskId = -1;
        }
    }
}