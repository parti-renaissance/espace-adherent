<?php

namespace App\Form;

use App\Committee\CommitteeCommand;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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
            ])
            ->add('phone', PhoneNumberType::class, [
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                'default_region' => 'FR',
                'preferred_country_choices' => ['FR'],
            ])
            ->add('facebookPageUrl', UrlType::class, [
                'required' => false,
                'default_protocol' => null,
            ])
            ->add('twitterNickname', TextType::class, [
                'required' => false,
            ])
        ;

        if (!$committee || $committee->isWaitingForApproval()) {
            $builder->add('photo', FileType::class, [
                'required' => $committee ? !$committee->hasPhotoUploaded() : true,
                'label' => false,
            ]);
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
