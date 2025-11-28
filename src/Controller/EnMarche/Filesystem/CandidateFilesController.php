<?php

declare(strict_types=1);

namespace App\Controller\EnMarche\Filesystem;

use App\AdherentSpace\AdherentSpaceEnum;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('ROLE_CANDIDATE') or (is_granted('ROLE_DELEGATED_CANDIDATE') and is_granted('HAS_DELEGATED_ACCESS_FILES'))"))]
#[Route(path: '/espace-candidat', name: 'app_candidate_files_', methods: ['GET'])]
class CandidateFilesController extends AbstractFilesController
{
    protected function getSpaceType(): string
    {
        return AdherentSpaceEnum::CANDIDATE;
    }
}
