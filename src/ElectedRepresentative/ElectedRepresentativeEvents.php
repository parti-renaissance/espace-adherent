<?php

namespace App\ElectedRepresentative;

final class ElectedRepresentativeEvents
{
    public const BEFORE_UPDATE = 'elected_representative.before_update';
    public const POST_UPDATE = 'elected_representative.post_update';

    private function __construct()
    {
    }
}
