<?php

namespace AppBundle\Form;

use AppBundle\Committee\CommitteeManager;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Geocoder\CoordinatesFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MembershipChooseNearbyCommitteeType extends AbstractType
{
    private $committeeManager;
    private $coordinatesFactory;

    public function __construct(CommitteeManager $committeeManager, CoordinatesFactory $coordinatesFactory)
    {
        $this->committeeManager = $committeeManager;
        $this->coordinatesFactory = $coordinatesFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $committees = $this->committeeManager->getNearbyCommittees($this->coordinatesFactory->createFromAdherent($options['adherent']));

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
                'memberships_count' => $committee['memberships_count'],
            ];
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('adherent')
            ->setAllowedTypes('adherent', Adherent::class)
        ;
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
