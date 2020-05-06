<?php

namespace App\Form\EventListener;

use App\Entity\AdherentMessage\Filter\AdherentZoneFilter;
use App\Form\FilterRoleType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class IncludeExcludeFilterRoleListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SET_DATA => 'entityToForm',
            FormEvents::SUBMIT => 'formToEntity',
        ];
    }

    public function entityToForm(FormEvent $event)
    {
        $includes = [];
        $excludes = [];

        /** @var AdherentZoneFilter $filter */
        $filter = $event->getData();
        foreach (FilterRoleType::ROLES as $role) {
            $roleIncluded = $filter->{'include'.$role}();

            if (true === $roleIncluded) {
                $includes[] = $role;
            } elseif (false === $roleIncluded) {
                $excludes[] = $role;
            }
        }

        $event->getForm()->get('includeRoles')->setData($includes);
        $event->getForm()->get('excludeRoles')->setData($excludes);
    }

    public function formToEntity(FormEvent $event)
    {
        /** @var AdherentZoneFilter $filter */
        $filter = $event->getData();
        $includedRoles = $event->getForm()->get('includeRoles')->getData() ?? [];
        $excludedRoles = $event->getForm()->get('excludeRoles')->getData() ?? [];

        foreach (FilterRoleType::ROLES as $role) {
            $include = null; // do not apply the filter
            if (\in_array($role, $includedRoles, true)) {
                $include = true; // include adherents with this role
            }

            if (\in_array($role, $excludedRoles, true)) {
                $include = false; // exclude adherents with this role
            }

            $filter->{'setInclude'.$role}($include);
        }
    }
}
