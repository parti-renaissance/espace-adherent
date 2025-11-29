<?php

declare(strict_types=1);

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Adherent;
use App\Entity\SubscriptionType;
use App\Subscription\SubscriptionTypeEnum;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class AdherentSubscriptionTypeExtension implements QueryCollectionExtensionInterface
{
    public function __construct(private readonly Security $security)
    {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (SubscriptionType::class !== $resourceClass) {
            return;
        }

        /** @var Adherent $adherent */
        $adherent = $this->security->getUser();

        if (!$adherent instanceof Adherent) {
            return;
        }

        if ($adherent->getAge() > 35) {
            $queryBuilder
                ->andWhere($queryBuilder->getRootAliases()[0].'.code != :jam_code')
                ->setParameter('jam_code', SubscriptionTypeEnum::JAM_EMAIL)
            ;
        }
    }
}
