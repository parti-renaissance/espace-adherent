<?php

namespace App\Form;

use App\Entity\Geo\Zone;
use App\Repository\Geo\ZoneRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class ZoneChoiceType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'geo_zone.zones',
                'class' => Zone::class,
                'required' => false,
                'multiple' => true,
                'managed_zones' => [],
                'query_builder' => static function (Options $options) {
                    $managed = $options['managed_zones'];

                    return static function (ZoneRepository $repository) use ($managed) {
                        return $repository
                            ->createSelectByManagedZones($managed)
                            ->orderBy('zone.type')
                        ;
                    };
                },
                'group_by' => function (Zone $zone): string {
                    return $this->translator->trans(sprintf('geo_zone.%s', $zone->getType()));
                },
            ])
            ->setAllowedTypes('managed_zones', 'array')
        ;
    }

    public function getParent(): string
    {
        return EntityType::class;
    }
}
