<?php

namespace App\Form;

use App\Entity\Event\EventCategory;
use App\Event\BaseEventCommand;
use App\Form\DataTransformer\EventDateTimeZoneTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BaseEventCommandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $beginDate = $builder->getData() ? $builder->getData()->getBeginAt() : null;
        $now = new \DateTime('now');
        $dateTimeOptions = [
            'min_date' => $beginDate && $beginDate < $now ? $beginDate : $now,
            'max_date' => new \DateTime('+5 years'),
            'minute_increment' => 15,
        ];

        $builder
            ->add('name', TextType::class, [
                'format_title_case' => true,
                'attr' => ['maxlength' => 100],
            ])
            ->add('category', EventCategoryType::class, [
                'class' => $options['event_category_class'],
            ])
            ->add('description', TextareaType::class)
            ->add('address', AddressType::class)
            ->add('timeZone', TimezoneType::class, [
                'choice_loader' => null,
                'choices' => $this->getTimezones(),
            ])
            ->add('beginAt', DateTimePickerType::class, $dateTimeOptions)
            ->add('finishAt', DateTimePickerType::class, $dateTimeOptions)
            ->add('capacity', IntegerType::class, [
                'required' => false,
                'attr' => ['min' => 1],
            ])
            ->add('visioUrl', UrlType::class, [
                'required' => false,
            ])
            ->add('image', CroppedImageType::class, [
                'required' => false,
                'label' => false,
                'image_path' => $options['image_path'],
                'ratio' => CroppedImageType::RATIO_16_9,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var BaseEventCommand $command */
            $command = $event->getData();

            if (null === $command->getBeginAt() || null === $command->getFinishAt()) {
                $beginDate = $this->createBeginDate();

                $command->setBeginAt($beginDate);
                $command->setFinishAt((clone $beginDate)->modify('+2 hours'));
            }
        });
        $builder->addModelTransformer(new EventDateTimeZoneTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'event_category_class' => EventCategory::class,
                'image_path' => null,
                'validation_groups' => ['Default', 'with_category'],
            ])
            ->setAllowedTypes('image_path', ['string', 'null'])
        ;
    }

    /**
     * Creates the beginning date in the future for the next minute step.
     * ex 1:
     *     if current time is 16h27m, the returned time will be 16h30m with these minutes steps: [0, 15, 30, 45]
     * ex 2:
     *     with time 16h52m it returns 17h00
     */
    protected function createBeginDate(): \DateTime
    {
        $now = new \DateTime();
        $nowMinute = $now->format('i');
        $step = null;

        foreach (['0', '15', '30', '45'] as $step) {
            if ($nowMinute <= $step) {
                break;
            }
        }

        if ($step < $nowMinute) {
            $now
                ->modify('next hour')
                ->modify(\sprintf('-%d minutes', $nowMinute))
            ;
        } else {
            $now->modify(\sprintf('+%d minutes', $step - $nowMinute));
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
