<?php

namespace App\CaptainVerify;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TimeoutExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CaptainVerifyDriver implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly Storage $storage,
        private readonly HttpClientInterface $captainVerifyClient,
        private readonly DenormalizerInterface $denormalizer,
        private readonly string $captainVerifyApiKey,
    ) {
    }

    public function verify(string $email): bool
    {
        if ($response = $this->storage->get($email)) {
            return $response->isValid();
        }

        $response = $this->makeRequest($email);

        if ($response->isSuccess()) {
            $this->storage->store($email, $response);
        }

        return $response->isValid();
    }

    private function makeRequest(string $email): Response
    {
        $response = new Response();

        try {
            $httpResponse = $this->captainVerifyClient->request('GET', '/v2/verify', ['query' => ['email' => $email, 'apikey' => $this->captainVerifyApiKey]]);
            $response = $this->denormalizer->denormalize($httpResponse->toArray(), Response::class);
        } catch (ExceptionInterface $e) {
            if (!$e->getPrevious() instanceof TimeoutExceptionInterface) {
                $this->logger->error('CaptainVerify API error : '.$e->getMessage());
            }
        }

        return $response;
    }
}
