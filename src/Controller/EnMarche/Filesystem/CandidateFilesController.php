<?php

namespace App\Controller\EnMarche\Filesystem;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-candidat", name="app_candidate_files_", methods={"GET"})
 *
 * @Security("is_granted('ROLE_CANDIDATE') or (is_granted('ROLE_DELEGATED_CANDIDATE') and is_granted('HAS_DELEGATED_ACCESS_FILES'))")
 */
class CandidateFilesController extends AbstractFilesController
{
    public const SPACE_NAME = 'candidate';

    protected function getSpaceType(): string
    {
        return self::SPACE_NAME;
    }
}
