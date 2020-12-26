<?php

declare(strict_types=1);

namespace paintball;

use paintball\block\BlockFactory;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase {

    /** @var Main */
    protected static $instance;

    public function onEnable(): void {
        self::$instance = $this;

        $this->getLogger()->info('PaintBallGun has been enable, loading config...');

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
    }

    public function onDisable(): void {
        $sleepTime = max(3, (int) (count($this->getServer()->getOnlinePlayers()) / 10));

        foreach (BlockFactory::getStorages() as $storage) {
            $storage->updateRestoreBlocks(true);

            BlockFactory::removeStorage($storage->getName());
        }

        sleep($sleepTime);
    }

    /**
     * @return Main
     */
    public static function getInstance(): self {
        return self::$instance;
    }
}