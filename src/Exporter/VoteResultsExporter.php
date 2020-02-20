<?php

namespace AppBundle\Exporter;

use AppBundle\Entity\VoteResult;
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
                    /** @var VoteResult $voteResult */
                    $voteResult = $data[0];

                    $abstentionsPercentage = $voteResult->getAbstentionsPercentage();
                    $expressedPercentage = $voteResult->getExpressedPercentage();
                    $votersPercentage = $voteResult->getVotersPercentage();

                    $fields = [
                        'Ville' => $voteResult->getVotePlace()->getCity(),
                        'Bureau' => $voteResult->getVotePlace()->getName(),
                        'Tour' => $voteResult->getElectionRound(),
                        'Inscrits' => $voteResult->getRegistered(),
                        'Abstentions' => $voteResult->getAbstentions(),
                        '% abstentions' => $abstentionsPercentage ? round($abstentionsPercentage, 2).' %' : null,
                        'Exprimés' => $voteResult->getExpressed(),
                        '% exprimés' => $expressedPercentage ? round($expressedPercentage, 2).' %' : null,
                        'Votants' => $voteResult->getVoters(),
                        '% votants' => $votersPercentage ? round($votersPercentage, 2).' %' : null,
                    ];

                    $listIndex = 1;
                    foreach ($voteResult->getLists() as $list) {
                        $listPercentage = round(($list['votes'] / $voteResult->getVoters()) * 100, 2);

                        $fields["Liste $listIndex"] = $list['label'];
                        $fields["Votes liste $listIndex"] = $list['votes'];
                        $fields["% liste $listIndex"] = $listPercentage.' %';

                        ++$listIndex;
                    }

                    return $fields;
                },
            )
        );
    }
}
