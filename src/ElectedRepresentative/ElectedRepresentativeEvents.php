<?php

namespace App\ElectedRepresentative;

final class ElectedRepresentativeEvents
{
    public const PRE_UPDATE = 'elected_representative.pre_update';
    public const POST_UPDATE = 'elected_representative.post_update';

    private function __construct()
    {
    }
}
