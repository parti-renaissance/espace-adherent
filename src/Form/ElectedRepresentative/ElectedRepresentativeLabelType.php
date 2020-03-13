<?php

namespace AppBundle\Form\ElectedRepresentative;

use AppBundle\Entity\ElectedRepresentative\ElectedRepresentativeLabel;
use AppBundle\Entity\PoliticalLabel;
use AppBundle\Repository\PoliticalLabelRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElectedRepresentativeLabelType extends AbstractType
{
    /** @var PoliticalLabelRepository */
    private $politicalLabelRepository;

    public function __construct(PoliticalLabelRepository $politicalLabelRepository)
    {
        $this->politicalLabelRepository = $politicalLabelRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('politicalLabel', TextType::class, [
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
                'choices' => ElectedRepresentativeLabel::getYears(),
                'choice_label' => function ($choice) {
                    return $choice;
                },
            ])
            ->add('finishYear', ChoiceType::class, [
                'required' => false,
                'label' => false,
                'placeholder' => '--',
                'choices' => ElectedRepresentativeLabel::getYears(),
                'choice_label' => function ($choice) {
                    return $choice;
                },
            ])
        ;

        $builder->get('politicalLabel')
            ->addModelTransformer(new CallbackTransformer(
                function ($politicalLabel) {
                    return $politicalLabel instanceof PoliticalLabel ? $politicalLabel->getName() : null;
                },
                function ($labelName) {
                    if ($labelName) {
                        if (!$politicalLabel = $this->politicalLabelRepository->findOneByName($labelName)) {
                            return new PoliticalLabel($labelName);
                        }

                        return $politicalLabel;
                    }

                    return $labelName;
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
            'data_class' => ElectedRepresentativeLabel::class,
        ]);
    }
}
