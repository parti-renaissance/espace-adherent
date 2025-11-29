<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\DataTransformer\AdherentToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdherentIdType extends AbstractType
{
    private $adherentToIdTransformer;

    public function __construct(AdherentToIdTransformer $adherentToIdTransformer)
    {
        $this->adherentToIdTransformer = $adherentToIdTransformer;
    }

    public function getParent(): string
    {
        return IntegerType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->adherentToIdTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'invalid_message' => 'adherent.unknown_id',
        ]);
    }
}
