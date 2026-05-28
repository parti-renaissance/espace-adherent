<?php

declare(strict_types=1);

namespace App\Form\Admin\NationalEvent;

use App\Entity\NationalEvent\NationalEvent;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NationalEventChoiceFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $allowedTypes = $options['allowed_types'];
        $forbiddenTypes = $options['forbidden_types'];

        $builder->add('event', EntityType::class, [
            'label' => 'Événement',
            'class' => NationalEvent::class,
            'choice_label' => 'name',
            'query_builder' => function (EntityRepository $repo) use ($allowedTypes, $forbiddenTypes): QueryBuilder {
                $qb = $repo->createQueryBuilder('e')->orderBy('e.startDate', 'DESC');

                if (null !== $allowedTypes && \count($allowedTypes) > 0) {
                    $qb->andWhere('e.type IN (:allowed_types)')->setParameter('allowed_types', $allowedTypes);
                }

                if (null !== $forbiddenTypes && \count($forbiddenTypes) > 0) {
                    $qb->andWhere('e.type NOT IN (:forbidden_types)')->setParameter('forbidden_types', $forbiddenTypes);
                }

                return $qb;
            },
            'required' => true,
            'placeholder' => '— Choisir un événement —',
            'data' => $options['preselected_event'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined(['allowed_types', 'forbidden_types', 'preselected_event'])
            ->setDefaults([
                'allowed_types' => null,
                'forbidden_types' => null,
                'preselected_event' => null,
            ])
            ->setAllowedTypes('allowed_types', ['null', 'array'])
            ->setAllowedTypes('forbidden_types', ['null', 'array'])
            ->setAllowedTypes('preselected_event', ['null', NationalEvent::class])
        ;
    }
}
