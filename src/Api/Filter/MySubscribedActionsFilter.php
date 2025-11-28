<?php

declare(strict_types=1);

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Action\Action;
use App\Entity\Action\ActionParticipant;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\Attribute\Required;

final class MySubscribedActionsFilter extends AbstractFilter
{
    private const PROPERTY_NAME = 'subscribedOnly';

    private Security $security;

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
            !is_a($resourceClass, Action::class, true)
            || self::PROPERTY_NAME !== $property
            || !$user = $this->security->getUser()
        ) {
            return;
        }

        $queryBuilder
            ->innerJoin(ActionParticipant::class, 'action_participant', Join::WITH, 'action_participant.action = '.$queryBuilder->getRootAliases()[0])
            ->andWhere('action_participant.adherent = :adherent')
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
