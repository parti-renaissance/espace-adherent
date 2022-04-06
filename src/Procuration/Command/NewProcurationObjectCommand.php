<?php

namespace App\Procuration\Command;

class NewProcurationObjectCommand implements ProcurationCommandInterface
{
    private int $id;
    private string $class;

    public function __construct(string $class, int $id)
    {
        $this->class = $class;
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getClass(): string
    {
        return $this->class;
    }
}
