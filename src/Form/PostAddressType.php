<?php

namespace App\Form;

use App\Address\Address;
use App\Entity\PostAddress;
use App\Form\DataTransformer\CityNameDataTransformer;
use App\FranceCities\FranceCities;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostAddressType extends AbstractType
{
    private FranceCities $franceCities;
    private CityNameDataTransformer $cityNameDataTransformer;

    public function __construct(FranceCities $franceCities, CityNameDataTransformer $cityNameDataTransformer)
    {
        $this->franceCities = $franceCities;
        $this->cityNameDataTransformer = $cityNameDataTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('address', TextType::class)
            ->add('cityName', TextType::class)
            ->add('country', UnitedNationsCountryType::class, [
                'preferred_choices' => [Address::FRANCE],
            ])
            ->add('postalCode', TextType::class)
        ;

        $builder
            ->addModelTransformer($this->cityNameDataTransformer)
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                /** @var Address $address */
                $address = $event->getData();

                if ($address && $address->getCityName() && $address->getPostalCode() && Address::FRANCE === $address->getCountry()) {
                    $city = $this->franceCities->getCityByPostalCodeAndName($address->getPostalCode(), $address->getCityName());

                    if ($city) {
                        $address->setCity(sprintf('%s-%s', $address->getPostalCode(), $city->getInseeCode()));
                    }
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => PostAddress::class,
                'error_bubbling' => false,
            ])
        ;
    }
}
