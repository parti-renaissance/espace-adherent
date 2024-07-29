<?php

namespace App\Form\AdherentMessage;

use App\Entity\ElectedRepresentative\LabelNameEnum;
use App\Entity\ElectedRepresentative\PoliticalFunctionNameEnum;
use App\Entity\UserListDefinition;
use App\Form\GenderType;
use App\Repository\UserListDefinitionRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class ElectedRepresentativeFilterType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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
            ->add('politicalFunction', ChoiceType::class, [
                'required' => false,
                'choices' => PoliticalFunctionNameEnum::CHOICES,
                'choice_label' => function (string $choice) {
                    return "elected_representative.mailchimp_tag.$choice";
                },
            ])
            ->add('userListDefinition', EntityType::class, [
                'required' => false,
                'class' => UserListDefinition::class,
                'query_builder' => function (UserListDefinitionRepository $repository) use ($options) {
                    return $repository->createQueryBuilder('ul')
                        ->where('ul.type IN (:types)')
                        ->setParameter('types', $options['user_list_types'])
                    ;
                },
                'choice_label' => function (UserListDefinition $choice) {
                    $key = 'elected_representative.mailchimp_tag.'.$choice->getCode();
                    $trad = $this->translator->trans($key);

                    if ($key === $trad) {
                        return \sprintf('[L] %s', $choice->getLabel());
                    }

                    return $trad;
                },
            ])
            ->add('label', ChoiceType::class, [
                'required' => false,
                'choices' => LabelNameEnum::ALL,
                'choice_label' => function (string $choice) {
                    return "elected_representative.mailchimp_tag.$choice";
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('user_list_types')
            ->setRequired('user_list_types')
            ->setAllowedTypes('user_list_types', ['array'])
        ;
    }
}
