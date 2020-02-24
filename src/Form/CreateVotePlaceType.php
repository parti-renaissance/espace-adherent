<?php

namespace AppBundle\Form;

use AppBundle\Address\Address;
use AppBundle\Entity\VotePlace;
use AppBundle\Intl\FranceCitiesBundle;
use AppBundle\Repository\VotePlaceRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateVotePlaceType extends AbstractType
{
    private $votePlaceRepository;

    public function __construct(VotePlaceRepository $votePlaceRepository)
    {
        $this->votePlaceRepository = $votePlaceRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('alias', TextType::class, ['required' => false])
            ->add('address', TextType::class)
            ->add('city', TextType::class)
            ->add('postalCode', TextType::class)
            ->add('country', UnitedNationsCountryType::class, [
                'placeholder' => 'Sélectionnez un pays',
                'preferred_choices' => [Address::FRANCE],
            ])
        ;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var VotePlace $votePlace */
            $votePlace = $event->getData();

            if (!$votePlace->getCountry() || (Address::FRANCE === $votePlace->getCountry() && !$votePlace->getPostalCode())) {
                return;
            }

            $codePrefix = 99999;

            if (Address::FRANCE === $votePlace->getCountry()) {
                $codePrefix = array_search(
                    $votePlace->getCity(),
                    FranceCitiesBundle::getPostalCodeCities($votePlace->getPostalCode()),
                    true
                );

                if (!$codePrefix) {
                    return;
                }
            }

            $lastVotePlace = $this->votePlaceRepository->findLastByCodePrefix($codePrefix);

            $codeParts = [
                $codePrefix,
                str_pad(
                    $lastVotePlace ? $lastVotePlace->getLocalCode() + 1 : 1,
                    4,
                    '0',
                    \STR_PAD_LEFT
                ),
            ];

            $votePlace->setCode(implode('_', $codeParts));
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VotePlace::class,
        ]);
    }
}
