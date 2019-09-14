<?php

namespace paintball;

use pocketmine\plugin\PluginBase;

class Main extends PluginBase {

    /** @var Main */
    protected static $instance;

    /** @var BlockData */
    protected $blockdata;

    public function onEnable(){
        self::$instance = $this;
        $this->getLogger()->info('PaintBallGun has been enable, loading config...');
        new EventListener();
        $this->blockdata = new BlockData();
    }

    /**
     * @return BlockData
     */
    public final function getBlockData(): BlockData {
        return $this->blockdata;
    }

    /**
     * @return Main
     */
    public static function getInstance(): self {
        return self::$instance;
    }
}