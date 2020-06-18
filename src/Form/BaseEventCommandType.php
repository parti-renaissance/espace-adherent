<?php

namespace App\Form;

use App\Entity\EventCategory;
use App\Event\BaseEventCommand;
use App\Form\DataTransformer\EventDateTimeZoneTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BaseEventCommandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'filter_emojis' => true,
                'format_title_case' => true,
                'attr' => ['maxlength' => 100],
            ])
            ->add('category', EventCategoryType::class, [
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
            ->add('capacity', IntegerType::class, [
                'required' => false,
                'attr' => ['min' => 1],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var BaseEventCommand $command */
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

    /**
     * Creates the begin date in the future for the next minute step.
     * ex 1:
     *     if current time is 16h27m, the returned time will be 16h30m with these minutes steps: [0, 15, 30, 45]
     * ex 2:
     *     with time 16h52m it returns 17h00
     */
    protected function createBeginDate(array $minutes): \DateTime
    {
        $now = new \DateTime();
        $nowMinute = $now->format('i');
        $step = null;

        foreach ($minutes as $step) {
            if ($nowMinute <= $step) {
                break;
            }
        }

        if ($step < $nowMinute) {
            $now
                ->modify('next hour')
                ->modify(sprintf('-%d minutes', $nowMinute))
            ;
        } else {
            $now->modify(sprintf('+%d minutes', $step - $nowMinute));
        }

        return $now;
    }

    protected function getTimezones()
    {
        $timezones = [];
        $dateTime = new \DateTime('now');
        foreach (\DateTimeZone::listIdentifiers() as $timezone) {
            $dateTime->setTimezone(new \DateTimeZone($timezone));
            $parts = explode('/', $timezone);
            $labelOffset = '(UTC '.$dateTime->format('P').')';

            if (\count($parts) > 2) {
                $region = $parts[0];
                $name = $parts[1].' - '.$parts[2];
            } elseif (\count($parts) > 1) {
                $region = $parts[0];
                $name = $parts[1];
            } else {
                $region = 'Other';
                $name = $parts[0];
            }
            $name .= ' '.$labelOffset;
            $timezones[$region][str_replace('_', ' ', $name)] = $timezone;
        }

        return 1 === \count($timezones) ? reset($timezones) : $timezones;
    }
}
