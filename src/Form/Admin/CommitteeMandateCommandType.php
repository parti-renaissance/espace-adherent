<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Admin\Committee\CommitteeAdherentMandateTypeEnum;
use App\Committee\DTO\CommitteeAdherentMandateCommand;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
use App\Form\AdherentIdType;
use App\ValueObject\Genders;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeMandateCommandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['types']) {
            $builder
                ->add('type', ChoiceType::class, [
                    'mapped' => false,
                    'choices' => $options['types'],
                    'choice_label' => function (string $choice) {
                        return 'adherent_mandate.committee.with_gender.'.$choice;
                    },
                ])
                ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
                    /** @var CommitteeAdherentMandateCommand $data */
                    $data = $event->getData();
                    $form = $event->getForm();

                    if (CommitteeMandateQualityEnum::SUPERVISOR === $data->getQuality() && $data->isProvisional()) {
                        $form->get('type')->setData(
                            Genders::FEMALE === $data->getGender()
                                ? CommitteeAdherentMandateTypeEnum::PROVISIONAL_SUPERVISOR_FEMALE
                                : CommitteeAdherentMandateTypeEnum::PROVISIONAL_SUPERVISOR_MALE
                        );
                    } elseif (!$data->getQuality() && false === $data->isProvisional()) {
                        $form->get('type')->setData(
                            Genders::FEMALE === $data->getGender()
                                ? CommitteeAdherentMandateTypeEnum::ELECTED_ADHERENT_FEMALE
                                : CommitteeAdherentMandateTypeEnum::ELECTED_ADHERENT_MALE
                        );
                    }
                })
                ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                    $data = $event->getData();
                    $form = $event->getForm();
                    /** @var CommitteeAdherentMandateCommand $model */
                    $model = $form->getData();

                    switch ($data['type']) {
                        case CommitteeAdherentMandateTypeEnum::PROVISIONAL_SUPERVISOR_FEMALE:
                            $model->setQuality(CommitteeMandateQualityEnum::SUPERVISOR);
                            $model->setProvisional(true);
                            $model->setGender(Genders::FEMALE);

                            break;
                        case CommitteeAdherentMandateTypeEnum::ELECTED_ADHERENT_FEMALE:
                            $model->setGender(Genders::FEMALE);
                            $model->setProvisional(false);

                            break;
                        case CommitteeAdherentMandateTypeEnum::PROVISIONAL_SUPERVISOR_MALE:
                            $model->setQuality(CommitteeMandateQualityEnum::SUPERVISOR);
                            $model->setProvisional(true);
                            $model->setGender(Genders::MALE);

                            break;
                        case CommitteeAdherentMandateTypeEnum::ELECTED_ADHERENT_MALE:
                            $model->setGender(Genders::MALE);
                            $model->setProvisional(false);

                            break;
                    }
                })
            ;
        }

        $builder
            ->add('adherent', AdherentIdType::class)
            ->add('confirm', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'types' => [],
            'data_class' => CommitteeAdherentMandateCommand::class,
        ])
            ->setAllowedTypes('types', 'array')
        ;
    }
}
