<?php

namespace App\Form\Procuration;

use App\Entity\Election;
use App\Procuration\ElectionContext;
use App\Repository\ElectionRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElectionContextType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ElectionContext::class,
            ])
        ;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('elections', EntityType::class, [
                'multiple' => true,
                'expanded' => true,
                'class' => Election::class,
                'query_builder' => function (ElectionRepository $repository) {
                    return $repository->createAllComingNextByRoundDateQueryBuilder();
                },
                'choice_label' => 'name',
            ])
            ->add('choose', SubmitType::class)
        ;
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['elections'] = [];

        foreach ($view['elections']->vars['choices'] as $choice) {
            $view->vars['elections'][] = $choice->data->getName();
        }
    }
}
