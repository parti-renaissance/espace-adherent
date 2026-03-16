<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\Adherent\Tag\StaticTag\TagBuilder;
use App\Adherent\Tag\TagTranslator;
use App\Scope\FeatureEnum;

class StaticTagsFilterBuilder extends AbstractTagsFilterBuilder
{
    public function __construct(TagTranslator $translator, private readonly TagBuilder $eventTagBuilder)
    {
        parent::__construct($translator);
    }

    protected function init(): void
    {
        $this->tags = $this->eventTagBuilder->buildAll();

        $this->fieldName = 'static_tags';
        $this->fieldLabel = 'Labels divers';
        $this->fullTag = false;
    }

    public function build(string $scope, ?string $feature = null, bool $isVox = false): array
    {
        $filters = parent::build($scope, $feature, $isVox);

        if ($isVox && FeatureEnum::CONTACTS === $feature) {
            foreach ($filters as $filter) {
                $filter->setPosition(2);
            }
        }

        return $filters;
    }
}
