<?php

namespace App\Adherent;

class CertificationPermissions
{
    public const CERTIFIED = 'adherent.certified';
    public const CERTIFY = 'certification.certify';
    public const UNCERTIFY = 'certification.uncertify';
    public const REQUEST = 'certification_request.request';
    public const APPROVE = 'certification_request.approve';
    public const REFUSE = 'certification_request.refuse';
    public const BLOCK = 'certification_request.block';

    public const REQUEST_PROCESS = [
        self::APPROVE,
        self::REFUSE,
        self::BLOCK,
    ];
}
