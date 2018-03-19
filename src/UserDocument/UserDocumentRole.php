<?php

namespace AppBundle\UserDocument;

use MyCLabs\Enum\Enum;

final class UserDocumentRole extends Enum
{
    public const ROLE_UPLOAD_REFERENT_DOCUMENT = 'ROLE_UPLOAD_REFERENT_DOCUMENT';
    public const ROLE_UPLOAD_COMMITTEE_DOCUMENT = 'ROLE_UPLOAD_COMMITTEE_DOCUMENT';
}
