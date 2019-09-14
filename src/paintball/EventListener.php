<?php

namespace paintball;

use pocketmine\block\Block;
use pocketmine\entity\projectile\Snowball;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\Server;

class EventListener implements Listener {

    /**
     * EventListener constructor.
     */
    public function __construct(){
        Server::getInstance()->getPluginManager()->registerEvents($this, Main::getInstance());
    }

    /**
     * @param PlayerQuitEvent $ev
     */
    public function onQuit(PlayerQuitEvent $ev): void {
        $player = $ev->getPlayer();
        if(Main::getInstance()->getBlockData()->get($player->getName()) instanceof BlockStorage) Main::getInstance()->getBlockData()->remove($player->getName());
    }

    /**
     * @param ProjectileHitBlockEvent $ev
     */
    public function onProjectileHit(ProjectileHitBlockEvent $ev){
        if($ev->getEntity() instanceof Snowball){
            $player = $ev->getEntity()->getOwningEntity();
            if($player instanceof Player){
                if(Main::getInstance()->getBlockData()->get($player->getName()) == null) $storage = Main::getInstance()->getBlockData()->add($player->getName());
                else $storage = Main::getInstance()->getBlockData()->get($player->getName());
                foreach(Main::getInstance()->getBlockData()->getBlocksInRadius($ev->getBlockHit()->asPosition(), 2) as $block){
                    $storage->add($block);
                    $ev->getEntity()->getLevel()->setBlock($block, Block::get(35, rand(0, 15)));
                }
                if($storage->restore == null) new Restore($storage);
            }
        }
    }

    public function microtime(int $value): void {
        $micro = (float) ($value * 1000) / 1;
        echo 'seconds > ' . $value . ':' . $micro;
    }
}