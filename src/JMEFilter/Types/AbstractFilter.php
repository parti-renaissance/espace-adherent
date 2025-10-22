<?php

namespace App\JMEFilter\Types;

use App\JMEFilter\FilterInterface;
use Symfony\Component\Serializer\Attribute\Groups;

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

    public function setHelp(?string $message): void
    {
        $this->addOption('help', $message);
    }

    public function setRequired(bool $value): void
    {
        $this->addOption('required', $value);
    }

    public function setFavorite(bool $value): void
    {
        $this->addOption('favorite', $value);
    }

    public function setAdvanced(bool $value): void
    {
        $this->addOption('advanced', $value);
    }

    public function setPlaceholder(?string $value): void
    {
        $this->addOption('placeholder', $value);
    }

    protected function addOption(string $key, $value): void
    {
        $this->options[$key] = $value;
    }

    abstract protected function _getType(): string;
}
