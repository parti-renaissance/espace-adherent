<?php

namespace App\Form;

use App\Entity\ReferentOrganizationalChart\ReferentPersonLink;
use App\Form\DataTransformer\CommitteeTransformer;
use App\Repository\AdherentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReferentPersonLinkType extends AbstractType
{
    private $adherentRepository;
    private $committeeTransformer;

    public function __construct(AdherentRepository $adherentRepository, CommitteeTransformer $committeeTransformer)
    {
        $this->adherentRepository = $adherentRepository;
        $this->committeeTransformer = $committeeTransformer;
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
                ],
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('restrictedCommittees', CollectionType::class, [
                'required' => false,
                'entry_type' => CommitteeUuidType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('restrictedCommittees_search', TextType::class, [
                'mapped' => false,
                'required' => false,
                'filter_emojis' => true,
                'attr' => [
                    'placeholder' => 'Ajouter le comité local',
                ],
            ])
            ->add('restrictedCities', CollectionType::class, [
                'required' => true,
                'entry_type' => TextType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('restrictedCities_search', SearchType::class, [
                'mapped' => false,
                'required' => false,
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
        $builder
            ->get('coReferent')
            ->addModelTransformer(new CallbackTransformer(
                function ($coReferent) {
                    return [$coReferent];
                },
                function ($coReferentChoices) {
                    if (\in_array(ReferentPersonLink::LIMITED_CO_REFERENT, $coReferentChoices)) {
                        return ReferentPersonLink::LIMITED_CO_REFERENT;
                    } elseif (\in_array(ReferentPersonLink::CO_REFERENT, $coReferentChoices)) {
                        return ReferentPersonLink::CO_REFERENT;
                    } else {
                        return null;
                    }
                }
            ))
        ;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            if (!$data instanceof ReferentPersonLink) {
                return;
            }

            if (!$data->isLimitedCoReferent()) {
                $data->emptyRestrictions();
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => false,
            'data_class' => ReferentPersonLink::class,
        ]);
    }
}
