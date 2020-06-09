<?php

namespace App\Controller\Api\UserListDefinition;

use App\Entity\ElectedRepresentative\ElectedRepresentative;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/elected-representative/user-list-definitions", name="app_elected_representative_")
 */
class ElectedRepresentativeUserListDefinitionController extends AbstractUserListDefinitionController
{
    public const MEMBER_TYPE = 'elected-representative';

    protected function getMemberEntityClass(): string
    {
        return ElectedRepresentative::class;
    }
}
