<?php

namespace App\Filter\Types;

use App\Filter\FilterInterface;
use Symfony\Component\Serializer\Annotation\Groups;

abstract class AbstractFilter implements FilterInterface
{
    private string $code;
    private string $label;
    private ?array $options = null;

    public function __construct(string $code, string $label)
    {
        $this->code = $code;
        $this->label = $label;
    }

    /** @Groups("filter:read") */
    public function getType(): string
    {
        return $this->_getType();
    }

    /** @Groups("filter:read") */
    public function getCode(): string
    {
        return $this->code;
    }

    /** @Groups("filter:read") */
    public function getLabel(): string
    {
        return $this->label;
    }

    /** @Groups("filter:read") */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    protected function addOption(string $key, $value): void
    {
        $this->options[$key] = $value;
    }

    abstract protected function _getType(): string;
}
