<?php

namespace App\Controller\Api\VotingPlatform;

use App\Entity\VotingPlatform\Designation\Designation;
use App\Repository\VotingPlatform\ElectionRepository;
use App\Repository\VotingPlatform\VoteResultRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sonata\Exporter\ExporterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/v3/designations/{uuid}/ballots.{_format}', name: 'app_designation_get_ballots', requirements: ['uuid' => '%pattern_uuid%', '_format' => 'json|xlsx'], defaults: ['_format' => 'json'], methods: ['GET'])]
#[Security("is_granted('REQUEST_SCOPE_GRANTED', 'designation')")]
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
