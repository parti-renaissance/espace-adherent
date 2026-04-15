<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Controller\Admin\ZoneAutocompleteController;
use App\Entity\Geo\Zone;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminZoneAutocompleteType extends AbstractType
{
    public function __construct(
        #[Autowire(service: 'sonata.admin.manager.orm')]
        private readonly ModelManagerInterface $modelManager,
    ) {
    }

    public function getParent(): string
    {
        return ModelAutocompleteType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'property' => ['code', 'name'],
                'class' => Zone::class,
                'model_manager' => $this->modelManager,
                'minimum_input_length' => 1,
                'items_per_page' => 25,
                'safe_label' => true,
                'zone_types' => [],
                'preset' => null,
                'route' => ['name' => ZoneAutocompleteController::ROUTE_NAME],
                'req_params' => [],
            ])
            ->setAllowedTypes('zone_types', ['array'])
            ->setAllowedTypes('preset', ['null', 'string'])
            ->setAllowedValues('preset', [null, ...ZoneAutocompleteController::PRESETS])
            ->setNormalizer('req_params', static function (Options $options, array $reqParams): array {
                if ($options['zone_types']) {
                    $reqParams['zone_types'] = implode(',', $options['zone_types']);
                }

                if ($options['preset']) {
                    $reqParams['preset'] = $options['preset'];
                }

                return $reqParams;
            })
        ;
    }
}
