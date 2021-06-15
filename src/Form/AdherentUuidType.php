<?php

namespace App\Form;

use App\Entity\Adherent;
use App\Form\DataTransformer\UuidToObjectTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class AdherentUuidType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new UuidToObjectTransformer($this->entityManager, Adherent::class));
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['adherent'] = ($data = $form->getData()) instanceof Adherent ? $data : null;
    }

    public function getParent()
    {
        return HiddenType::class;
    }
}
