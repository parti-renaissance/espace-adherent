<?php

namespace App\Form\AdherentMessage;

use App\Entity\Geo\Zone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class ReferentFilterType extends AbstractType
{
    public function getParent()
    {
        return AdvancedMessageFilterType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('contactOnlyVolunteers', CheckboxType::class, ['required' => false]);

        if ($this->hasParisZone($options['zones'])) {
            $builder->add('contactOnlyRunningMates', CheckboxType::class, ['required' => false]);
        }
    }

    private function hasParisZone(array $zones): bool
    {
        return !empty(array_filter($zones, function (Zone $zone) {
            return str_starts_with($zone->getCode(), '75');
        }));
    }
}
