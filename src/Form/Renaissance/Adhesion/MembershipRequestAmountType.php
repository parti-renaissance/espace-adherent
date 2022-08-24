<?php

namespace App\Form\Renaissance\Adhesion;

use App\Form\AmountType;
use App\Renaissance\Membership\MembershipRequestCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MembershipRequestAmountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', AmountType::class)
            ->add('otherAmount', IntegerType::class, [
                'required' => false,
                'scale' => 2,
            ])
            ->add('predefinedAmount', ChoiceType::class, [
                'placeholder' => false,
                'choices' => [
                    30 => 30,
                    60 => 60,
                    120 => 120,
                    500 => 500,
                ],
                'required' => false,
                'expanded' => true,
            ])
        ;

        $builder->add('membership_amount', SubmitType::class, ['label' => 'Étape suivante']);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var MembershipRequestCommand $data */
            $data = $event->getData();

            if ($amount = $data->getAmount()) {
                if (\in_array((int) $amount, [30, 60, 120, 500], true)) {
                    $data->setPredefinedAmount((int) $amount);
                } else {
                    $data->setOtherAmount($amount);
                }

                $event->setData($data);
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            if (isset($data['otherAmount']) && is_numeric($data['otherAmount'])) {
                $data['amount'] = (float) $data['otherAmount'];
                unset($data['otherAmount'], $data['predefinedAmount']);
                $event->setData($data);
            }

            if (isset($data['predefinedAmount']) && is_numeric($data['predefinedAmount'])) {
                $data['amount'] = (float) $data['predefinedAmount'];
                unset($data['otherAmount'], $data['predefinedAmount']);
                $event->setData($data);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => MembershipRequestCommand::class,
                'validation_groups' => 'membership_request_amount',
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'app_renaissance_membership';
    }
}
