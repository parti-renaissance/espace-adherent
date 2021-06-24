<?php

namespace App\Form\Admin;

use App\Entity\Instance\AdherentInstanceQuality;
use App\Entity\Instance\InstanceQuality;
use App\Repository\Instance\InstanceQualityRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdherentInstanceQualityType extends AbstractType implements DataTransformerInterface
{
    private $repository;
    private $persistingQualities = [];

    public function __construct(InstanceQualityRepository $repository)
    {
        $this->repository = $repository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this);
    }

    public function getParent()
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'required' => false,
            'class' => InstanceQuality::class,
            'query_builder' => $this->repository->getCustomQualitiesQueryBuilder(),
            'choice_label' => 'getFullLabel',
       ]);
    }

    public function transform($value)
    {
        if ($value instanceof Collection) {
            /** @var AdherentInstanceQuality[] $value */
            $this->persistingQualities = $qualities = [];

            foreach ($value as $adherentQuality) {
                $quality = $adherentQuality->getInstanceQuality();

                if ($quality->isCustom()) {
                    $qualities[] = $quality;
                } else {
                    $this->persistingQualities[] = $adherentQuality;
                }
            }

            return current($qualities);
        }

        return $value;
    }

    public function reverseTransform($value)
    {
        return array_merge($this->persistingQualities, $value instanceof InstanceQuality ? [$value] : []);
    }
}
