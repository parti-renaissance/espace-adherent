<?php

namespace AppBundle\Form;

use AppBundle\Entity\EventCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BaseEventCommandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'filter_emojis' => true,
                'format_title_case' => true,
            ])
            ->add('category', EventCategoryType::class, [
                'class' => $options['event_category_class'],
            ])
            ->add('description', PurifiedTextareaType::class, [
                'filter_emojis' => true,
                'purifier_type' => 'enrich_content',
            ])
            ->add('address', AddressType::class)
            ->add('beginAt', DateTimeType::class, [
                'years' => $options['years'],
                'minutes' => $options['minutes'],
            ])
            ->add('finishAt', DateTimeType::class, [
                'years' => $options['years'],
                'minutes' => $options['minutes'],
            ])
            ->add('capacity', IntegerType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $years = range(date('Y'), date('Y') + 5);

        $resolver
            ->setDefaults([
                'years' => array_combine($years, $years),
                'minutes' => [
                    '00' => '0',
                    '15' => '15',
                    '30' => '30',
                    '45' => '45',
                ],
                'event_category_class' => EventCategory::class,
            ])
        ;
    }
}
