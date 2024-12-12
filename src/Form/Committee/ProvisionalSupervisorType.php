<?php

namespace App\Form\Committee;

use App\Entity\Adherent;
use App\Repository\AdherentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProvisionalSupervisorType extends AbstractType implements DataTransformerInterface
{
    private $adherentRepository;

    public function __construct(AdherentRepository $adherentRepository)
    {
        $this->adherentRepository = $adherentRepository;
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this);
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['label'] = ($data = $form->getData()) instanceof Adherent ? $data->getFullName() : '';
        $view->vars['value'] = ($data = $form->getData()) instanceof Adherent ? $data->getId() : null;
        $view->vars['gender'] = $options['gender'];
    }

    public function transform($value): mixed
    {
        return $value;
    }

    public function reverseTransform($value): mixed
    {
        return !empty($value) ? $this->adherentRepository->find($value) : $value;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Adherent::class,
            'gender' => null,
        ])
            ->setAllowedTypes('gender', ['null', 'string'])
        ;
    }
}
