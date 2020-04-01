<?php

namespace AppBundle\Form\AdherentMessage;

use AppBundle\Entity\AdherentMessage\Filter\AdherentZoneFilter;
use AppBundle\Form\CommitteeChoiceType;
use AppBundle\Form\GenderType;
use AppBundle\Form\MemberInterestsChoiceType;
use AppBundle\Repository\CommitteeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdherentZoneFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('includeAdherentsNoCommittee', CheckboxType::class, ['required' => false])
            ->add('includeAdherentsInCommittee', CheckboxType::class, ['required' => false])
            ->add('includeCommitteeHosts', CheckboxType::class, ['required' => false])
            ->add('includeCommitteeSupervisors', CheckboxType::class, ['required' => false])
            ->add('includeCitizenProjectHosts', CheckboxType::class, ['required' => false])
            ->add('gender', GenderType::class, [
                'placeholder' => 'Tous',
                'expanded' => true,
                'required' => false,
            ])
            ->add('ageMin', IntegerType::class, ['required' => false])
            ->add('ageMax', IntegerType::class, ['required' => false])
            ->add('firstName', TextType::class, ['required' => false])
            ->add('lastName', TextType::class, ['required' => false])
            ->add('city', TextType::class, ['required' => false])
            ->add('interests', MemberInterestsChoiceType::class, ['required' => false, 'expanded' => false])
            ->add('registeredSince', DateType::class, ['required' => false, 'widget' => 'single_text', 'html5' => true])
            ->add('registeredUntil', DateType::class, ['required' => false, 'widget' => 'single_text', 'html5' => true])
            ->add('committee', CommitteeChoiceType::class, [
                'required' => false,
                'query_builder' => static function (CommitteeRepository $repository) use ($options) {
                    return $repository->getQueryBuilderForTags($options['referent_tags']);
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => AdherentZoneFilter::class,
                'referent_tags' => null,
            ])
            ->setAllowedTypes('referent_tags', ['array'])
            ->setRequired(['referent_tags'])
        ;
    }
}
