<?php

namespace AppBundle\Form;

use AppBundle\Entity\InstitutionalEventCategory;
use AppBundle\Form\DataTransformer\EventDateTimeZoneTransformer;
use AppBundle\InstitutionalEvent\InstitutionalEventCommand;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InstitutionalEventCommandType extends BaseEventCommandType
{
    public function getParent()
    {
        return BaseEventCommandType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'filter_emojis' => true,
                'format_title_case' => true,
            ])
            ->add('category', InstitutionalEventCategoryType::class, [
                'class' => $options['event_category_class'],
            ])
            ->add('description', PurifiedTextareaType::class, [
                'filter_emojis' => true,
                'purifier_type' => 'enrich_content',
            ])
            ->add('address', AddressType::class)
            ->add('timeZone', TimezoneType::class, [
                'choices' => $this->getTimezones(),
            ])
            ->add('beginAt', DateTimeType::class, [
                'years' => $options['years'],
                'minutes' => $options['minutes'],
            ])
            ->add('finishAt', DateTimeType::class, [
                'years' => $options['years'],
                'minutes' => $options['minutes'],
            ])
            ->remove('capacity')
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var InstitutionalEventCommand $command */
            $command = $event->getData();

            if (null === $command->getBeginAt() || null === $command->getFinishAt()) {
                $beginDate = $this->createBeginDate($event->getForm()->getConfig()->getOption('minutes'));

                $command->setBeginAt($beginDate);
                $command->setFinishAt((clone $beginDate)->modify('+2 hours'));
            }
        });

        $builder->addModelTransformer(new EventDateTimeZoneTransformer());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => InstitutionalEventCommand::class,
                'event_category_class' => InstitutionalEventCategory::class,
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'institutional_event';
    }
}
