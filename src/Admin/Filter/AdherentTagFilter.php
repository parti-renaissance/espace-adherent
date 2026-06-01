<?php

declare(strict_types=1);

namespace App\Admin\Filter;

use App\Adherent\Tag\TagEnum;
use App\Adherent\Tag\TagTranslator;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\Operator\ContainsOperatorType;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Contracts\Service\Attribute\Required;

class AdherentTagFilter extends AbstractCallbackDecoratorFilter
{
    private TagTranslator $tagTranslator;

    #[Required]
    public function setTagTranslator(TagTranslator $tagTranslator): void
    {
        $this->tagTranslator = $tagTranslator;
    }

    protected function getTags(): array
    {
        return $this->getOptions()['tags'] ?? [];
    }

    protected function getInitialFilterOptions(): array
    {
        return [
            'field_type' => ChoiceType::class,
            'operator_type' => ContainsOperatorType::class,
            'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                if (!$value->hasValue()) {
                    return false;
                }

                $orX = $qb->expr()->orX();

                $notContains = ContainsOperatorType::TYPE_NOT_CONTAINS === $value->getType();

                foreach ($value->getValue() as $index => $choice) {
                    $exactParam = 'tag_exact_'.$field.'_'.$index;
                    $childParam = 'tag_child_'.$field.'_'.$index;

                    if ($notContains) {
                        $orX->add(\sprintf('(FIND_IN_SET(:%s, %s.tags) = 0 AND %s.tags NOT LIKE :%s)', $exactParam, $alias, $alias, $childParam));
                    } else {
                        $orX->add(\sprintf('(FIND_IN_SET(:%s, %s.tags) > 0 OR %s.tags LIKE :%s)', $exactParam, $alias, $alias, $childParam));
                    }

                    $qb->setParameter($exactParam, $choice);
                    $qb->setParameter($childParam, '%'.$choice.':%');
                }

                $qb->andWhere($orX);

                return true;
            },
            'field_options' => [
                'multiple' => true,
                'choice_label' => function (string $tag) {
                    $label = $this->tagTranslator->trans($tag, false, '_filter_');

                    if ($count = (substr_count($tag, ':') - substr_count($tag, TagEnum::NATIONAL_EVENT_PRESENT) * 2)) {
                        return \sprintf('%s• %s', str_repeat("\u{a0}", $count * 3), $label);
                    }

                    return $label;
                },
            ],
        ];
    }

    protected function getFilterOptionsForRendering(): array
    {
        $filterOptions = $this->getOptions();

        if ($tags = $this->getTags()) {
            $filterOptions['field_options']['choices'] = $tags;
        }

        return $filterOptions;
    }
}
