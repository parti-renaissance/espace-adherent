<?php

namespace App\Form;

use App\Address\Address;
use App\Committee\CommitteeCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeCommandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $command = $builder->getData();
        $committee = $command instanceof CommitteeCommand ? $command->getCommittee() : null;
        $builder
            ->add('name', TextType::class, [
                'filter_emojis' => true,
                'format_title_case' => true,
                'disabled' => $committee ? $committee->isNameLocked() : false,
            ])
            ->add('description', TextareaType::class, [
                'filter_emojis' => true,
            ])
            ->add('address', AddressType::class, [
                'disable_fields' => $committee ? $committee->isApproved() : false,
                'child_error_bubbling' => false,
                'data' => $builder->getData() ? Address::createFromAddress($builder->getData()->getAddress()) : null,
            ])
        ;

        if ($committee) {
            $builder
                ->add('facebookPageUrl', UrlType::class, [
                    'required' => false,
                    'default_protocol' => null,
                ])
                ->add('twitterNickname', TextType::class, [
                    'required' => false,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CommitteeCommand::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'committee';
    }
}
