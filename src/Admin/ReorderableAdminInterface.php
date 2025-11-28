<?php

declare(strict_types=1);

namespace App\Admin;

interface ReorderableAdminInterface
{
    public function getListMapperEndColumns(): array;
}
