<?php

namespace App\Controller\Api\VotingPlatform;

use App\Entity\VotingPlatform\Designation\Designation;
use App\Repository\VotingPlatform\ElectionRepository;
use App\Repository\VotingPlatform\VoterRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sonata\Exporter\ExporterInterface;
use Sonata\Exporter\Source\IteratorCallbackSourceIterator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/v3/designations/{uuid}/voters.{_format}', name: 'app_designation_get_voters', requirements: ['uuid' => '%pattern_uuid%', '_format' => 'json|xlsx'], defaults: ['_format' => 'json'], methods: ['GET'])]
#[Security("is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'designation')")]
class ElectionVotersListController extends AbstractController
{
    public function __invoke(
        Designation $designation,
        ElectionRepository $electionRepository,
        VoterRepository $voterRepository,
        ExporterInterface $exporter,
        string $_format = 'json',
    ): Response {
        $voters = [];
        if ($election = $electionRepository->findOneByDesignation($designation)) {
            $voters = $voterRepository->getVotersForElection($election);
        }

        if ('json' !== $_format) {
            $voters = array_filter($voters, fn ($voter) => $voter['voted_at']);

            return $exporter->getResponse(
                $_format,
                \sprintf('Emargements - %d.xlsx', \count($voters)),
                new IteratorCallbackSourceIterator(
                    new \ArrayIterator($voters),
                    fn ($voter) => [
                        'Prénom' => $voter['first_name'],
                        'Nom' => $voter['last_name'],
                        'Date, heure d\'émargement' => $voter['voted_at'],
                        'Code postal' => $voter['postal_code'],
                    ]
                )
            );
        }

        return $this->json($voters);
    }
}
