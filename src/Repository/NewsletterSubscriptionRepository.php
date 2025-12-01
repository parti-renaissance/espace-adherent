<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\NewsletterSubscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\NewsletterSubscription>
 */
class NewsletterSubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsletterSubscription::class);
    }

    public function findById(int $id): ?NewsletterSubscription
    {
        return $this->disableSoftDeleteableFilter()->find($id);
    }

    public function findOneByEmail(string $email): ?NewsletterSubscription
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function disableSoftDeleteableFilter(): self
    {
        if ($this->_em->getFilters()->has('softdeleteable') && $this->_em->getFilters()->isEnabled('softdeleteable')) {
            $this->_em->getFilters()->disable('softdeleteable');
        }

        return $this;
    }

    public function createQueryBuilderForSynchronization(): QueryBuilder
    {
        return $this->disableSoftDeleteableFilter()->createQueryBuilder('newsletter');
    }
}
