<?php

namespace Tests\App\Behat\Context;

use App\Entity\Donation;
use App\Repository\DonationRepository;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\MinkExtension\Context\RawMinkContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Tests\App\Test\Payment\PayboxProvider;

class DonationContext extends RawMinkContext
{
    private const DEFAULT_SESSION_NAME = 'default';

    /**
     * @When I simulate IPN call with :status code for the last donation of :email
     */
    public function simulateIpnCall(string $status, string $email): void
    {
        $sessionName = $this->getMink()->getDefaultSessionName();
        $this->getMink()->setDefaultSessionName(self::DEFAULT_SESSION_NAME);

        if (!$donation = $this->getDonation($email)) {
            throw new \Exception(\sprintf('Donation not found for email %s', $email));
        }

        /** @var PayboxProvider $payboxProvider */
        $payboxProvider = $this->getService(PayboxProvider::class);
        $data = $payboxProvider->prepareCallbackParameters($donation->getUuid()->toString(), $status);

        HttpClient::create()->request('POST', 'http://'.$this->getParameter('webhook_renaissance_host').'/paybox/payment-ipn/'.time(), [
            'body' => $data,
        ]);

        $this->getMink()->setDefaultSessionName($sessionName);
    }

    private function getService(string $name)
    {
        return $this->getContainer()->get($name);
    }

    private function getParameter(string $name)
    {
        return $this->getContainer()->getParameter($name);
    }

    private function getDonation(string $email): ?Donation
    {
        /** @var DonationRepository $repository */
        $repository = $this->getService(DonationRepository::class);

        return $repository->createQueryBuilder('d')
            ->innerJoin('d.donator', 'donator')
            ->andWhere('donator.emailAddress = :email')
            ->setParameter('email', $email)
            ->orderBy('d.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    private function getContainer(): ContainerInterface
    {
        $driver = $this->getMink()->getSession()->getDriver();

        if (!$driver instanceof BrowserKitDriver) {
            throw new \RuntimeException(\sprintf('Driver must be one instance of %s', BrowserKitDriver::class));
        }

        return $driver->getClient()->getContainer();
    }
}
