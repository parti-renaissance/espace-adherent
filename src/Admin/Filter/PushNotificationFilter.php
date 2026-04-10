<?php

declare(strict_types=1);

namespace App\Admin\Filter;

use App\Entity\Notification;
use App\Entity\PushNotification;
use App\Form\Admin\PushNotificationAutocompleteType;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\DoctrineORMAdminBundle\Filter\Filter;

/**
 * Filtre les entités qui ont été touchées par une PushNotification.
 *
 * Option `join_path` : liste des relations Doctrine à joindre depuis l'alias
 * racine jusqu'à l'entité PushToken (propriété `identifier`).
 *
 * Exemple pour Adherent : ['appSessions', 'pushTokenLinks', 'pushToken']
 * Exemple pour AppSession : ['pushTokenLinks', 'pushToken']
 */
class PushNotificationFilter extends Filter
{
    public function filter(ProxyQueryInterface $query, string $alias, string $field, FilterData $data): void
    {
        if (!$data->hasValue()) {
            $this->setActive(false);

            return;
        }

        $current = $alias;
        foreach ($this->getOption('join_path') as $i => $relation) {
            $joinAlias = 'pnf_'.$i;
            $query->innerJoin(\sprintf('%s.%s', $current, $relation), $joinAlias);
            $current = $joinAlias;
        }

        $query
            ->andWhere(\sprintf('EXISTS (
                SELECT 1 FROM %s pnf_n
                JOIN pnf_n.pushTokens pnf_pt
                WHERE pnf_n.pushNotification = :pushNotification
                AND pnf_pt = %s
            )', Notification::class, $current))
            ->setParameter('pushNotification', $data->getValue())
        ;

        $this->setActive(true);
    }

    public function getDefaultOptions(): array
    {
        return [
            'field_type' => PushNotificationAutocompleteType::class,
            'field_options' => [
                'class' => PushNotification::class,
            ],
            'join_path' => [],
        ];
    }

    public function getFormOptions(): array
    {
        return [
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label' => $this->getLabel(),
        ];
    }
}
