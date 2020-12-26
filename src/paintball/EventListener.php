<?php

declare(strict_types=1);

namespace paintball;

use paintball\block\BlockFactory;
use pocketmine\block\Block;
use pocketmine\entity\projectile\Snowball;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\Server;

class EventListener implements Listener {

    /**
     * @param PlayerQuitEvent $ev
     *
     * @priority NORMAL
     */
    public function onPlayerQuitEvent(PlayerQuitEvent $ev): void {
        $player = $ev->getPlayer();

        BlockFactory::removeStorage($player->getName());
    }

    /**
     * @param ProjectileHitBlockEvent $ev
     *
     * @priority NORMAL
     */
    public function onProjectileHitBlockEvent(ProjectileHitBlockEvent $ev): void {
        $entity = $ev->getEntity();

        if (!$entity instanceof Snowball) return;

        $player = $entity->getOwningEntity();

        if (!$player instanceof Player) return;

        if (!$player->hasPermission('snow.paintball')) return;

        if ($player->getLevelNonNull() !== Server::getInstance()->getDefaultLevel()) return;

        $storage = BlockFactory::getStorage($player->getName());

        if ($storage == null) $storage = BlockFactory::addStorage($player->getName());

        foreach (BlockFactory::getBlocksInRadius($ev->getBlockHit(), 2) as $blocksInRadius) {
            $storage->addBlock($blocksInRadius);

            $player->getLevelNonNull()->setBlock($blocksInRadius, Block::get(35, rand(0, 15)));
        }
    }
}