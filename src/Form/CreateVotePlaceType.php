<?php

namespace App\Form;

use App\Address\AddressInterface;
use App\Entity\Election\VotePlace;
use App\FranceCities\FranceCities;
use App\Repository\Election\VotePlaceRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateVotePlaceType extends AbstractType
{
    private $votePlaceRepository;
    private FranceCities $franceCities;

    public function __construct(VotePlaceRepository $votePlaceRepository, FranceCities $franceCities)
    {
        $this->votePlaceRepository = $votePlaceRepository;
        $this->franceCities = $franceCities;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('alias', TextType::class, ['required' => false])
            ->add('address', TextType::class)
            ->add('city', TextType::class)
            ->add('postalCode', TextType::class)
            ->add('country', CountryType::class, [
                'placeholder' => 'Sélectionnez un pays',
                'preferred_choices' => [AddressInterface::FRANCE],
            ])
        ;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var VotePlace $votePlace */
            $votePlace = $event->getData();

            if (!$votePlace->getCountry() || (AddressInterface::FRANCE === $votePlace->getCountry() && !$votePlace->getPostalCode())) {
                return;
            }

            $codePrefix = 99999;

            if (AddressInterface::FRANCE === $votePlace->getCountry()) {
                $codePrefix = array_search(
                    $votePlace->getCity(),
                    $this->getCodesAndCitiesNameFromPostalCode($votePlace->getPostalCode()),
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

    private function getCodesAndCitiesNameFromPostalCode(string $postalCode): array
    {
        $cities = $this->franceCities->findCitiesByPostalCode($postalCode);

        $codeAndNameArray = [];
        foreach ($cities as $city) {
            $codeAndNameArray[$city->getInseeCode()] = $city->getName();
        }

        return $codeAndNameArray;
    }
}
