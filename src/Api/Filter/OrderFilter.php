<?php

namespace App\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter as BaseOrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class OrderFilter extends BaseOrderFilter
{
    private ?NameConverterInterface $nameConverter = null;

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
        dump($context, $this->nameConverter);
        if ($this->nameConverter && isset($context['filters'][$this->orderParameterName]) && is_array($context['filters'][$this->orderParameterName])) {
            foreach ($context['filters'][$this->orderParameterName] as $property => $value) {
                $normalizedProperty = $this->nameConverter->normalize($property);
                unset($context['filters'][$this->orderParameterName][$property]);
                $context['filters'][$this->orderParameterName][$normalizedProperty] = $value;
            }
        }

        dd($context);

        parent::apply(
            $queryBuilder,
            $queryNameGenerator,
            $resourceClass,
            $operationName,
            $context
        );
    }
}
