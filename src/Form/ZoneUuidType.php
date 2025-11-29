<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Geo\Zone;
use App\Form\DataTransformer\UuidToObjectTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class ZoneUuidType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new UuidToObjectTransformer($this->entityManager, Zone::class));
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['zone'] = ($data = $form->getData()) instanceof Zone ? $data : null;
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }
}
