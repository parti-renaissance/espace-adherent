<?php

namespace App\ChezVous\Command;

class UpdateMeasureTypeOnAlgoliaCommand extends AbstractMeasureTypeOnAlgoliaCommand
{
    public function __construct(public int $measureTypeId)
    {
    }
}
