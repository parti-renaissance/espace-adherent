<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnregistrationReasonsChoiceType extends AbstractType
{
    private $reasonsChoices;

    public function __construct(array $reasons)
    {
        $this->reasonsChoices = $reasons;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();

            if ($data) {
                foreach ($data as $i => $reason) {
                    if (!isset($this->reasonsChoices[$reason])) {
                        // We need to remove existing value in database
                        // in case the config has changed
                        unset($data[$i]);
                    }
                }

                $event->setData($data);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => array_flip($this->reasonsChoices),
            'expanded' => true,
            'multiple' => true,
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
