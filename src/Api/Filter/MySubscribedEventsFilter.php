<?php

namespace App\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\EventRegistration;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

final class MySubscribedEventsFilter extends AbstractContextAwareFilter
{
    private const PROPERTY_NAME = 'subscribedOnly';

    /** @var Security */
    private $security;

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        if (
            !is_a($resourceClass, BaseEvent::class, true)
            || self::PROPERTY_NAME !== $property
            || !$user = $this->security->getUser()
        ) {
            return;
        }

        $queryBuilder
            ->innerJoin(EventRegistration::class, 'event_registration', Join::WITH, 'event_registration.event = '.$queryBuilder->getRootAliases()[0])
            ->andWhere('event_registration.adherentUuid = :adherent_uuid')
            ->setParameter('adherent_uuid', $user->getUuid())
        ;
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'subscribedOnly' => [
                'property' => null,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }

    /** @required */
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }
}
