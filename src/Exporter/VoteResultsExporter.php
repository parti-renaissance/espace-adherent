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

                    $fields = [
                        'Ville' => $voteResult->getVotePlace()->getCity(),
                        'Bureau' => $voteResult->getVotePlace()->getName(),
                        'Tour' => $voteResult->getElectionRound(),
                        'Inscrits' => $voteResult->getRegistered(),
                        'Abstentions' => $voteResult->getAbstentions(),
                        '% abstentions' => $voteResult->getAbstentionsPercentage().' %',
                        'Exprimés' => $voteResult->getExpressed(),
                        '% exprimés' => $voteResult->getExpressedPercentage().' %',
                        'Votants' => $voteResult->getVoters(),
                        '% votants' => $voteResult->getVotersPercentage().' %',
                    ];

                    $listIndex = 1;
                    foreach ($voteResult->getLists() as $list) {
                        $fields["Liste $listIndex"] = $list['label'];
                        $fields["Votes liste $listIndex"] = $list['votes'];

                        ++$listIndex;
                    }

                    return $fields;
                },
            )
        );
    }
}
