<?php

namespace App\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter as BaseOrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class OrderFilter extends BaseOrderFilter
{
    private ?NameConverterInterface $nameConverter = null;

    /**
     * @required
     */
    public function setNameConverter(NameConverterInterface $nameConverter)
    {
        $this->nameConverter = $nameConverter;
    }

    public function apply(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null,
        array $context = []
    ) {
        if ($this->nameConverter && isset($context['filters'][$this->orderParameterName]) && \is_array($context['filters'][$this->orderParameterName])) {
            foreach ($context['filters'][$this->orderParameterName] as $property => $value) {
                $normalizedProperty = $this->nameConverter->denormalize($property);
                unset($context['filters'][$this->orderParameterName][$property]);
                $context['filters'][$this->orderParameterName][$normalizedProperty] = $value;
            }
        }

        parent::apply(
            $queryBuilder,
            $queryNameGenerator,
            $resourceClass,
            $operationName,
            $context
        );
    }
}
