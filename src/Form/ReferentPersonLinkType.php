<?php

namespace AppBundle\Form;

use AppBundle\Entity\ReferentOrganizationalChart\ReferentPersonLink;
use AppBundle\Repository\AdherentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReferentPersonLinkType extends AbstractType
{
    private $adherentRepository;

    public function __construct(AdherentRepository $adherentRepository)
    {
        $this->adherentRepository = $adherentRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'form_full' => true,
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'form_full' => true,
            ])
            ->add('email', TextType::class, [
                'label' => 'E-mail',
                'form_full' => true,
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone',
                'form_full' => true,
            ])
            ->add('postalAddress', TextType::class, [
                'label' => 'Adresse postale',
                'form_full' => true,
            ])
            ->add('coReferent', ChoiceType::class, [
                'choices' => [
                    'referent.radio.co_referent' => ReferentPersonLink::CO_REFERENT,
                    'referent.radio.limited_co_referent' => ReferentPersonLink::LIMITED_CO_REFERENT,
                    'referent.radio.not_co_referent' => null,
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('isJecouteManager', CheckboxType::class, [
                'required' => false,
            ])
            ->add('isMunicipalManagerSupervisor', CheckboxType::class, [
                'required' => false,
            ])
        ;

        $builder->addModelTransformer(new CallbackTransformer(
            function ($value) { return $value; },
            function ($value) {
                /** @var ReferentPersonLink $value */
                if (!$value->getEmail() || (($adherent = $value->getAdherent()) && $adherent->getEmailAddress() === $value->getEmail())) {
                    return $value;
                }

                $value->setAdherent($this->adherentRepository->findOneByEmail($value->getEmail()));

                return $value;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => false,
            'data_class' => ReferentPersonLink::class,
        ]);
    }
}
