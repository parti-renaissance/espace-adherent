<?php

declare(strict_types=1);

namespace App\Admin\Filter;

use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\Filter;

abstract class AbstractCallbackDecoratorFilter extends Filter
{
    public function __construct(protected readonly CallbackFilter $decorated)
    {
    }

    final public function filter(ProxyQueryInterface $query, string $alias, string $field, FilterData $data): void
    {
        $this->decorated->filter($query, $alias, $field, $data);

        $this->setActive($this->decorated->isActive());
    }

    final public function getDefaultOptions(): array
    {
        $options = array_merge($this->decorated->getDefaultOptions(), $this->getInitialFilterOptions());

        // Initialize decorated Callback filter
        $this->decorated->initialize($this->getName(), $options);

        return $options;
    }

    final public function getRenderSettings(): array
    {
        [$type, $options] = $this->decorated->getRenderSettings();

        return [$type, array_merge(
            $options,
            array_intersect_key(
                $this->getFilterOptionsForRendering(),
                array_flip(['field_type', 'field_options', 'operator_type', 'operator_options', 'label']),
            ),
        )];
    }

    protected function getFilterOptionsForRendering(): array
    {
        return $this->getOptions();
    }

    abstract protected function getInitialFilterOptions(): array;
}
