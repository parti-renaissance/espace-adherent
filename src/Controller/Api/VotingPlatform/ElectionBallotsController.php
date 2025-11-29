<?php

declare(strict_types=1);

namespace App\Controller\Api\VotingPlatform;

use App\Entity\VotingPlatform\Designation\Designation;
use App\Repository\VotingPlatform\ElectionRepository;
use App\Repository\VotingPlatform\VoteResultRepository;
use Sonata\Exporter\ExporterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('REQUEST_SCOPE_GRANTED', 'designation')"))]
#[Route('/v3/designations/{uuid}/ballots.{_format}', name: 'app_designation_get_ballots', requirements: ['uuid' => '%pattern_uuid%', '_format' => 'json|xlsx'], defaults: ['_format' => 'json'], methods: ['GET'])]
class ElectionBallotsController extends AbstractController
{
    public function __invoke(
        Designation $designation,
        ElectionRepository $electionRepository,
        VoteResultRepository $voteResultRepository,
        ExporterInterface $exporter,
        string $_format = 'json',
    ): Response {
        $ballots = $voteResultRepository->getVotes($designation);

        if ('json' !== $_format) {
            return $exporter->getResponse($_format, \sprintf('Bulletins - %s.xlsx', $designation->getTitle()), new \ArrayIterator($ballots));
        }

        return $this->json($ballots);
    }
}
