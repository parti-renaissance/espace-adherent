<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Donation;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\DonationMessage;
use AppBundle\Repository\TransactionRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TransactionCallbackHandler
{
    private $router;
    private $entityManager;
    private $mailer;
    private $donationRequestUtils;
    private $transactionRepository;

    public function __construct(
        UrlGeneratorInterface $router,
        ObjectManager $entityManager,
        MailerService $mailer,
        DonationRequestUtils $donationRequestUtils,
        TransactionRepository $transactionRepository
    ) {
        $this->router = $router;
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->donationRequestUtils = $donationRequestUtils;
        $this->transactionRepository = $transactionRepository;
    }

    public function handle(string $uuid, Request $request, string $callbackToken): Response
    {
        $donation = $this->entityManager->getRepository(Donation::class)->findOneByUuid($uuid);

        if (!$donation) {
            return new RedirectResponse($this->router->generate('donation_index'));
        }

        $payload = $this->donationRequestUtils->extractPayboxResultFromCallback($request, $callbackToken);
        $transactionId = $payload['transaction'];

        if (!$transactionId || !$transaction = $this->transactionRepository->findByPayboxTransactionId($transactionId)) {
            $this->entityManager->persist($transaction = $donation->processPayload($payload));

            $this->entityManager->flush();

            $campaignExpired = (bool) $request->attributes->get('_campaign_expired', false);
            if (!$campaignExpired && $transaction->isSuccessful()) {
                $this->mailer->sendMessage(DonationMessage::create($transaction));
            }
        }

        return new RedirectResponse($this->router->generate(
            'donation_result',
            $this->donationRequestUtils->createCallbackStatus($transaction)
        ));
    }
}
