<?php

namespace App\Exporter;

use App\Entity\Election\VotePlaceResult;
use Doctrine\ORM\Query;
use Sonata\Exporter\Exporter as SonataExporter;
use Sonata\Exporter\Source\IteratorCallbackSourceIterator;
use Symfony\Component\HttpFoundation\Response;

class VoteResultsExporter
{
    private $exporter;

    public function __construct(SonataExporter $exporter)
    {
        $this->exporter = $exporter;
    }

    public function getResponse(string $format, Query $query): Response
    {
        return $this->exporter->getResponse(
            $format,
            sprintf('resultats-votes--%s.%s', date('d-m-Y--H-i'), $format),
            new IteratorCallbackSourceIterator(
                $query->iterate(),
                function (array $data) {
                    /** @var VotePlaceResult $voteResult */
                    $voteResult = $data[0];

                    $abstentionsPercentage = $voteResult->getAbstentionsPercentage();
                    $expressedPercentage = $voteResult->getExpressedPercentage();
                    $participatedPercentage = $voteResult->getParticipatedPercentage();

                    $fields = [
                        'Ville' => $voteResult->getVotePlace()->getCity(),
                        'Bureau' => $voteResult->getVotePlace()->getName(),
                        'Tour' => $voteResult->getElectionRound(),
                        'Inscrits' => $voteResult->getRegistered(),
                        'Abstentions' => $voteResult->getAbstentions(),
                        '% abstentions' => $abstentionsPercentage ? round($abstentionsPercentage, 2).' %' : null,
                        'Exprimés' => $voteResult->getExpressed(),
                        '% exprimés' => $expressedPercentage ? round($expressedPercentage, 2).' %' : null,
                        'Participés' => $voteResult->getParticipated(),
                        '% participés' => $participatedPercentage ? round($participatedPercentage, 2).' %' : null,
                    ];

                    $listIndex = 1;
                    foreach ($voteResult->getListTotalResults() as $result) {
                        $list = $result->getList();
                        $listPercentage = round(($result->getTotal() / $voteResult->getParticipated()) * 100, 2);

                        $fields["Liste $listIndex"] = $list->getLabel();
                        $fields["Votes liste $listIndex"] = $result->getTotal();
                        $fields["% liste $listIndex"] = $listPercentage.' %';
                        $fields["Nuance liste $listIndex"] = $list->getNuance();
                        $fields["Adhérents LaREM sur la liste $listIndex"] = $list->getAdherentCount();
                        $fields["Adhérents éligible la liste $listIndex"] = $list->getEligibleCount();

                        ++$listIndex;
                    }

                    return $fields;
                },
            )
        );
    }
}
