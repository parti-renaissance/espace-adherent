<?php

namespace AppBundle\Form;

use AppBundle\Entity\ReferentTag;
use AppBundle\ManagedUsers\ManagedUsersFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ManagedUsersFilterType extends AbstractType
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
            ->add('emailSubscription', ChoiceType::class, [
                'choices' => [
                    'common.all' => null,
                    'common.adherent.subscribed' => true,
                    'common.adherent.unsubscribed' => false,
                ],
            ])

            ->add('sort', HiddenType::class, ['required' => false])
            ->add('order', HiddenType::class, ['required' => false])
        ;

        if (false === $options['single_zone']) {
            $builder->add('referentTags', MyReferentTagChoiceType::class, [
                'placeholder' => 'Tous',
                'required' => false,
                'by_reference' => false,
            ]);

            $referentTagsField = $builder->get('referentTags');

            $referentTagsField->addModelTransformer(new CallbackTransformer(
                static function ($value) use ($referentTagsField) {
                    if (\is_array($value) && \count($value) === \count($referentTagsField->getOption('choices'))) {
                        return null;
                    }

                    return $value;
                },
                static function ($value) use ($referentTagsField) {
                    if (null === $value) {
                        return  $referentTagsField->getOption('choices');
                    }

                    if ($value instanceof ReferentTag) {
                        return [$value];
                    }

                    return $value;
                },
            ));
        }
    }

    public function getBlockPrefix()
    {
        return 'f';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ManagedUsersFilter::class,
                'single_zone' => false,
                'allow_extra_fields' => true,
            ])
            ->setAllowedTypes('single_zone', ['bool'])
        ;
    }
}
