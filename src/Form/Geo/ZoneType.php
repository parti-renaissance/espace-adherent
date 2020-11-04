<?php

namespace App\Form\Geo;

use App\Entity\Geo\Zone;
use App\Repository\Geo\ZoneRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class ZoneType extends AbstractType implements DataTransformerInterface
{
    private $zoneRepository;

    public function __construct(ZoneRepository $zoneRepository)
    {
        $this->zoneRepository = $zoneRepository;
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
        $view->vars['zone_label'] = ($data = $form->getData()) instanceof Zone ? $data->getName() : '';
    }

    public function transform($value)
    {
        return $value instanceof Zone ? $value->getId() : $value;
    }

    public function reverseTransform($value)
    {
        return !empty($value) ? $this->zoneRepository->find($value) : $value;
    }
}
