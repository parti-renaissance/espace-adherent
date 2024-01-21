<?php

namespace App\Repository;

use App\Entity\TelegramBot;
use App\Telegram\BotInterface;
use App\Telegram\BotProviderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TelegramBotRepository extends ServiceEntityRepository implements BotProviderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TelegramBot::class);
    }

    public function findOneByUuid(string $uuid): ?TelegramBot
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }

    public function loadByIdentifier(string $identifier): ?BotInterface
    {
        return $this->findOneByUuid($identifier);
    }

    public function findOneEnabledBySecret(string $secret): ?TelegramBot
    {
        return $this->createQueryBuilder('telegram_bot')
            ->where('telegram_bot.enabled = :enabled')
            ->andWhere('telegram_bot.secret = :secret')
            ->setParameters([
                'enabled' => true,
                'secret' => $secret,
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
