<?php

namespace App\JMEFilter\Types;

use App\JMEFilter\FilterInterface;
use Symfony\Component\Serializer\Annotation\Groups;

abstract class AbstractFilter implements FilterInterface
{
    private string $code;
    private string $label;
    private int $position = 0;
    private ?array $options = null;

    public function __construct(string $code, string $label)
    {
        $this->code = $code;
        $this->label = $label;
    }

    #[Groups('filter:read')]
    public function getType(): string
    {
        return $this->_getType();
    }

    #[Groups('filter:read')]
    public function getCode(): string
    {
        return $this->code;
    }

    #[Groups('filter:read')]
    public function getLabel(): string
    {
        return $this->label;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    #[Groups('filter:read')]
    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setRequired(bool $value): void
    {
        $this->addOption('required', $value);
    }

    public function setFavorite(bool $value): void
    {
        $this->addOption('favorite', $value);
    }

    protected function addOption(string $key, $value): void
    {
        $this->options[$key] = $value;
    }

    abstract protected function _getType(): string;
}
