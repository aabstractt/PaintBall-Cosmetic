<?php


namespace paintball;

use pocketmine\Player;
use pocketmine\scheduler\Task;

class Restore extends Task {

    /** @var BlockStorage */
    protected $storage;

    /**
     * Restore constructor.
     * @param BlockStorage $storage
     */
    public function __construct(BlockStorage $storage){
        $this->storage = $storage;
        $this->storage->restore = $this;
        Main::getInstance()->getScheduler()->scheduleRepeatingTask($this, 20);
    }

    /**
     * Actions to execute when run
     *
     * @param int $currentTick
     *
     * @return void
     */
    public function onRun(int $currentTick){
        if(!$this->storage->getPlayer() instanceof Player) return;
        foreach($this->storage->getAll() as $data){
            if((time() - $data['time']) > 4) $this->storage->restore($data['pos']);
        }
        if(count($this->storage->getAll()) < 1){
            $this->getHandler()->cancel();
            $this->storage->restore = null;
            echo 'canceñ' . PHP_EOL;
        }
    }
}