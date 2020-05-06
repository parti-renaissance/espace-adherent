<?php

namespace App\Procuration;

use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProcurationRequestSerializer
{
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    public function serialize(array $exportedRequests): string
    {
        if (!is_iterable($exportedRequests)) {
            throw new \InvalidArgumentException();
        }

        // Create URLs
        foreach ($exportedRequests as $key => $request) {
            $processedAt = $request['request_processedAt']->format('Y-m-d H:i:s');

            $token = $processedAt.$request['proposal_id'];
            $exportedRequests[$key]['generatedUrl'] = $this->router->generate('app_procuration_my_request', [
                'id' => $request['request_id'],
                'token' => Uuid::uuid5(Uuid::NAMESPACE_OID, $token)->toString(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            $exportedRequests[$key]['request_processedAt'] = $processedAt;
        }

        $handle = fopen('php://memory', 'rb+');
        fputcsv($handle, [
            'request_id',
            'request_birthdate',
            'request_firstNames',
            'request_lastName',
            'request_emailAddress',
            'request_address',
            'request_postalCode',
            'request_city',
            'request_cityName',
            'request_country',
            'request_voteOffice',
            'request_votePostalCode',
            'request_voteCity',
            'request_voteCityName',
            'request_voteCountry',
            'request_electionPresidentialFirstRound',
            'request_electionPresidentialSecondRound',
            'request_electionLegislativeFirstRound',
            'request_electionLegislativeSecondRound',
            'request_reason',
            'request_processedAt',
            'proposal_id',
            'proposal_birthdate',
            'proposal_firstNames',
            'proposal_lastName',
            'proposal_emailAddress',
            'proposal_address',
            'proposal_postalCode',
            'proposal_city',
            'proposal_cityName',
            'proposal_country',
            'proposal_voteOffice',
            'proposal_votePostalCode',
            'proposal_voteCity',
            'proposal_voteCityName',
            'proposal_voteCountry',
            'proposal_electionPresidentialFirstRound',
            'proposal_electionPresidentialSecondRound',
            'proposal_electionLegislativeFirstRound',
            'proposal_electionLegislativeSecondRound',
            'generatedUrl',
        ]);

        foreach ($exportedRequests as $exportedRequest) {
            $exportedRequest['request_electionPresidentialFirstRound'] = (int) $exportedRequest['request_electionPresidentialFirstRound'];
            $exportedRequest['request_electionPresidentialSecondRound'] = (int) $exportedRequest['request_electionPresidentialSecondRound'];
            $exportedRequest['request_electionLegislativeFirstRound'] = (int) $exportedRequest['request_electionLegislativeFirstRound'];
            $exportedRequest['request_electionLegislativeSecondRound'] = (int) $exportedRequest['request_electionLegislativeSecondRound'];
            $exportedRequest['proposal_electionPresidentialFirstRound'] = (int) $exportedRequest['proposal_electionPresidentialFirstRound'];
            $exportedRequest['proposal_electionPresidentialSecondRound'] = (int) $exportedRequest['proposal_electionPresidentialSecondRound'];
            $exportedRequest['proposal_electionLegislativeFirstRound'] = (int) $exportedRequest['proposal_electionLegislativeFirstRound'];
            $exportedRequest['proposal_electionLegislativeSecondRound'] = (int) $exportedRequest['proposal_electionLegislativeSecondRound'];

            fputcsv($handle, $exportedRequest);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
