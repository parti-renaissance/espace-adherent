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
    private InstanceQualityRepository $repository;
    private array $persistingQualities = [];
    private ?AdherentInstanceQuality $currentAdherentQuality = null;

    public function __construct(InstanceQualityRepository $repository)
    {
        $this->repository = $repository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this);
    }

    public function getParent(): ?string
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

    public function transform($value): mixed
    {
        if ($value instanceof Collection) {
            /** @var AdherentInstanceQuality[] $value */
            $this->persistingQualities = $qualities = [];

            foreach ($value as $adherentQuality) {
                $quality = $adherentQuality->getInstanceQuality();

                if ($quality->isCustom()) {
                    $qualities[] = $adherentQuality;
                } else {
                    $this->persistingQualities[] = $adherentQuality;
                }
            }

            $this->currentAdherentQuality = $qualities ? current($qualities) : null;

            return $this->currentAdherentQuality ? $this->currentAdherentQuality->getInstanceQuality() : null;
        }

        return $value;
    }

    public function reverseTransform($value): mixed
    {
        if ($value instanceof InstanceQuality) {
            return array_merge($this->persistingQualities, [
                $this->currentAdherentQuality && $this->currentAdherentQuality->getInstanceQuality() === $value ?
                    $this->currentAdherentQuality
                    : new AdherentInstanceQuality(null, $value),
            ]);
        }

        return $this->persistingQualities;
    }
}
