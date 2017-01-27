<?php

namespace AppBundle\Form;

use AppBundle\Entity\Committee;
use AppBundle\Committee\CommitteeNearbyProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class MembershipChooseNearbyCommitteeType extends AbstractType
{
    private $provider;

    /**
     * @param CommitteeNearbyProvider $provider
     */
    public function __construct(CommitteeNearbyProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $committees = $this->provider->findNearbyCommittees(3);

        $builder
            ->add('committees', ChoiceType::class, [
                'choices' => self::getChoices($committees),
                'choice_name' => function ($choice) {
                    return $choice;
                },
                'multiple' => true,
                'expanded' => true,
            ])
            ->setAttribute('committees', $committees)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $committees = $form->getConfig()->getAttribute('committees');
        foreach ($committees as $name => $committee) {
            $view->vars['committees_views_data'][$name] = [
                'slug' => $committee['committee']->getSlug(),
                'memberships' => $committee['memberships'],
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'app_membership_choose_nearby_committee';
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private static function getChoices(array $data): array
    {
        foreach ($data as $row) {
            /** @var Committee $committee */
            $committee = $row['committee'];
            $choices[$committee->getName()] = (string) $committee->getUuid();
        }

        return $choices ?? [];
    }
}
