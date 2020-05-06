<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberInterestsChoiceType extends AbstractType
{
    private $interestsChoices;

    public function __construct(array $interests)
    {
        $this->interestsChoices = $interests;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();

            if ($data) {
                foreach ($data as $i => $interest) {
                    if (!isset($this->interestsChoices[$interest])) {
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
            'choices' => array_flip($this->interestsChoices),
            'expanded' => true,
            'multiple' => true,
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
