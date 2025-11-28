<?php

declare(strict_types=1);

namespace App\Twig\Components;

trait StatusAwareTrait
{
    public ?string $status = 'default';
    public ?string $message;
    public ?string $validate;
    public ?string $onCheck;
}
