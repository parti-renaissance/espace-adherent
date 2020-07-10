<?php

namespace App\Controller\EnMarche\ElectedRepresentative;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-senateur", name="app_senator_elected_representatives_")
 * @Security("is_granted('ROLE_SENATOR')")
 */
class SenatorElectedRepresentativeController extends AbstractElectedRepresentativeController
{
    protected function getSpaceType(): string
    {
        return 'senator';
    }

    protected function getManagedTags(Request $request): array
    {
        return [$this->getUser()->getSenatorArea()->getDepartmentTag()];
    }
}
