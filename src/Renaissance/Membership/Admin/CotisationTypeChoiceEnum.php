<?php

namespace App\Renaissance\Membership\Admin;

enum CotisationTypeChoiceEnum
{
    public const TYPE_CHECK = 'check';
    public const TYPE_TPE = 'tpe';

    public const CHOICES = [
        self::TYPE_CHECK,
        self::TYPE_TPE,
    ];
}
