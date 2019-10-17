<?php

namespace AppBundle\Form\AdherentMessage;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentMessage\Filter\MunicipalChiefFilter;
use AppBundle\Form\GenderType;
use AppBundle\Intl\FranceCitiesBundle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class MunicipalChiefFilterType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gender', GenderType::class, [
                'placeholder' => 'Tous',
                'expanded' => true,
                'required' => false,
            ])
            ->add('firstName', TextType::class, ['required' => false])
            ->add('lastName', TextType::class, ['required' => false])
            ->add('contactRunningMateTeam', CheckboxType::class, ['required' => false])
            ->add('contactVolunteerTeam', CheckboxType::class, ['required' => false])
            ->add('contactOnlyRunningMates', CheckboxType::class, ['required' => false])
            ->add('contactOnlyVolunteers', CheckboxType::class, ['required' => false])
            ->add('contactAdherents', CheckboxType::class, ['required' => false])
        ;

        if ($this->isSpecialCityMunicipalChief()) {
            $builder->add('postalCode', TextType::class, ['required' => false]);
        } else {
            $builder->add('contactNewsletter', CheckboxType::class, ['required' => false]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MunicipalChiefFilter::class,
        ]);
    }

    private function isSpecialCityMunicipalChief(): bool
    {
        /** @var $user Adherent */
        return
            ($user = $this->security->getUser()) instanceof Adherent
            && $user->isMunicipalChief()
            && isset(FranceCitiesBundle::SPECIAL_CITY_ZONES[$user->getMunicipalChiefManagedArea()->getInseeCode()])
        ;
    }
}
