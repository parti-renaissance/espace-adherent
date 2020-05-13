<?php

namespace App\Form;

use App\Committee\CommitteeManager;
use App\Entity\Committee;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeAroundAdherentType extends AbstractType
{
    private $manager;

    public function __construct(CommitteeManager $manager)
    {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('committees', ChoiceType::class, [
                'choices' => $this->getChoices($options['committees']),
                'choice_name' => function ($choice) {
                    return $choice;
                },
                'multiple' => true,
                'expanded' => true,
            ])
            ->setAttribute('committees', $options['committees'])
        ;
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $committees = $form->getConfig()->getAttribute('committees');

        /** @var Committee $committee */
        foreach ($committees as $committee) {
            $view->vars['committees_views_data'][$committee->getUuid()->toString()] = [
                'slug' => $committee->getSlug(),
                'members_count' => $committee->getMembersCount(),
            ];
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'committees' => [],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_membership_choose_committees_around_adherent';
    }

    private function getChoices(array $committees): array
    {
        foreach ($committees as $committee) {
            /** @var Committee $committee */
            $choices[$committee->getName()] = $committee->getUuid()->toString();
        }

        return $choices ?? [];
    }
}
