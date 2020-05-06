<?php

namespace App\Form;

use App\Entity\Summary;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SummaryItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $item = $builder->getData();

        if ($options['summary'] instanceof Summary) {
            $builder->add('display_order', SummaryItemPositionType::class, [
                'item' => $item,
                'collection' => $options['collection'],
            ]);
        }

        if ($options['add_submit_button']) {
            $builder->add('submit', SubmitType::class, [
                'label' => $item ? 'Modifier' : 'Ajouter',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'summary' => null,
                'collection' => null,
                'add_submit_button' => true,
            ])
            ->setAllowedTypes('summary', ['null', Summary::class])
            ->setAllowedTypes('add_submit_button', 'bool')
        ;
    }
}
