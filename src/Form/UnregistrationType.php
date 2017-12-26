<?php

namespace AppBundle\Form;

use AppBundle\Membership\UnregistrationCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnregistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('comment', TextareaType::class, [
                'required' => false,
                'filter_emojis' => true,
            ])
            ->add('reasons', UnregistrationReasonsChoiceType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UnregistrationCommand::class,
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();

                if (in_array('autre', $data->getReasons())) {
                    return ['Default', 'Reason'];
                }

                return ['Default'];
            },
        ]);
    }
}
