<?php

namespace AppBundle\Form\ElectedRepresentative;

use AppBundle\Entity\ElectedRepresentative\Label;
use AppBundle\Entity\ElectedRepresentative\LabelName;
use AppBundle\Repository\ElectedRepresentative\LabelNameRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LabelType extends AbstractType
{
    /** @var LabelNameRepository */
    private $labelNameRepository;

    public function __construct(LabelNameRepository $labelNameRepository)
    {
        $this->labelNameRepository = $labelNameRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Saisissez le nom d\'une étiquette',
                ],
            ])
            ->add('onGoing', CheckboxType::class, [
                'required' => false,
                'label' => false,
            ])
            ->add('beginYear', ChoiceType::class, [
                'label' => false,
                'placeholder' => '--',
                'choices' => Label::getYears(),
                'choice_label' => function ($choice) {
                    return $choice;
                },
            ])
            ->add('finishYear', ChoiceType::class, [
                'required' => false,
                'label' => false,
                'placeholder' => '--',
                'choices' => Label::getYears(),
                'choice_label' => function ($choice) {
                    return $choice;
                },
            ])
        ;

        $builder->get('name')
            ->addModelTransformer(new CallbackTransformer(
                function ($nameLabel) {
                    return $nameLabel instanceof LabelName ? $nameLabel->getName() : null;
                },
                function ($name) {
                    if ($name) {
                        if (!$labelName = $this->labelNameRepository->findOneByName($name)) {
                            return new LabelName($name);
                        }

                        return $labelName;
                    }

                    return $name;
                }
            ))
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            if ((isset($data['onGoing']) && '1' === $data['onGoing'])) {
                unset($data['finishYear']);
                $event->setData($data);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Label::class,
        ]);
    }
}
