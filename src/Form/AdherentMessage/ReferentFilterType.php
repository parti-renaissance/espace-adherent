<?php

namespace AppBundle\Form\AdherentMessage;

use AppBundle\AdherentSegment\AdherentSegmentTypeEnum;
use AppBundle\Entity\AdherentMessage\Filter\ReferentUserFilter;
use AppBundle\Form\AdherentSegmentType;
use AppBundle\Form\GenderType;
use AppBundle\Form\MemberInterestsChoiceType;
use AppBundle\Form\MyReferentTagChoiceType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class ReferentFilterType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

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
            ->add('contactOnlyVolunteers', CheckboxType::class, ['required' => false])
            ->add('adherentSegment', AdherentSegmentType::class, [
                'required' => false,
                'query_builder' => function (EntityRepository $repository) {
                    return $repository->createQueryBuilder('segment')
                        ->where('segment.author = :author')
                        ->andWhere('segment.segmentType = :type')
                        ->setParameters([
                            'author' => $this->security->getUser(),
                            'type' => AdherentSegmentTypeEnum::TYPE_REFERENT,
                        ])
                    ;
                },
            ])
        ;

        if (false === $options['is_referent_from_paris']) {
            $builder->add('contactOnlyRunningMates', CheckboxType::class, ['required' => false]);
        }

        if (false === $options['single_zone']) {
            $builder->add('referentTags', MyReferentTagChoiceType::class, ['multiple' => true]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ReferentUserFilter::class,
                'single_zone' => false,
                'is_referent_from_paris' => false,
            ])
            ->setAllowedTypes('single_zone', ['bool'])
            ->setAllowedTypes('is_referent_from_paris', ['bool'])
        ;
    }
}
