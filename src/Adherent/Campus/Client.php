<?php

namespace App\Adherent\Campus;

use App\Entity\Adherent;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Client implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly NormalizerInterface $normalizer,
        private readonly HttpClientInterface $client
    ) {
    }

    public function sendRegistrationRequest(Adherent $adherent): void
    {
        $body = $this->normalizer->normalize(AdherentValueObject::createFromAdherent($adherent), 'array', ['groups' => ['adherent_campus']]);

        try {
            $this->client->request('POST', '', ['json' => $body]);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error(sprintf('[API] Error: %s', $e->getMessage()), ['exception' => $e]);
        }
    }
}
