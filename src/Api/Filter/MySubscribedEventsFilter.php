<?php

declare(strict_types=1);

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Event\Event;
use App\Entity\Event\EventRegistration;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\Attribute\Required;

final class MySubscribedEventsFilter extends AbstractFilter
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
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (
            !is_a($resourceClass, Event::class, true)
            || self::PROPERTY_NAME !== $property
            || !$user = $this->security->getUser()
        ) {
            return;
        }

        $queryBuilder
            ->innerJoin(EventRegistration::class, 'event_registration', Join::WITH, 'event_registration.event = '.$queryBuilder->getRootAliases()[0])
            ->andWhere('event_registration.adherent = :adherent')
            ->setParameter('adherent', $user)
        ;
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            self::PROPERTY_NAME => [
                'property' => null,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }

    #[Required]
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }
}
