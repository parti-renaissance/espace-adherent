<?php

namespace App\CaptainVerify;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CaptainVerifyDriver implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly Storage $storage,
        private readonly HttpClientInterface $httpClient,
        private readonly DenormalizerInterface $denormalizer,
        private readonly string $captainVerifyApiKey
    ) {
    }

    public function verify(string $email): Response
    {
        if ($response = $this->storage->get($email)) {
            return $response;
        }

        $response = $this->makeRequest($email);

        if ($response->isSuccess()) {
            $this->storage->store($email, $response);
        } else {
            $this->logger->error('CaptainVerify API error', ['response' => $response]);
        }

        return $response;
    }

    private function makeRequest(string $email): Response
    {
        $httpResponse = $this->httpClient->request('GET', "/v2/verify?email=$email&apikey=$this->captainVerifyApiKey");

        return $this->denormalizer->denormalize($httpResponse->toArray(), Response::class);
    }
}
