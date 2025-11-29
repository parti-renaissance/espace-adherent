<?php

declare(strict_types=1);

namespace Tests\App\Behat\Context;

use App\Donation\Command\ReceivePayboxIpnResponseCommand;
use App\Entity\Donation;
use App\Repository\DonationRepository;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\MinkExtension\Context\RawMinkContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Tests\App\Test\Payment\PayboxProvider;

class DonationContext extends RawMinkContext
{
    private const DEFAULT_SESSION_NAME = 'default';

    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

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

        $this->bus->dispatch(new ReceivePayboxIpnResponseCommand($data));

        $this->getMink()->setDefaultSessionName($sessionName);
    }

    /**
     * @When I am on payment status page for the last donation of :email
     */
    public function IAmOnPaymentStatusPage(string $email): void
    {
        $sessionName = $this->getMink()->getDefaultSessionName();
        $this->getMink()->setDefaultSessionName(self::DEFAULT_SESSION_NAME);

        if (!$donation = $this->getDonation($email)) {
            throw new \Exception(\sprintf('Donation not found for email %s', $email));
        }

        $this->getMink()->setDefaultSessionName($sessionName);
        $this->visitPath('/paiement?result='.$donation->getTransactions()->first()->getPayboxResultCode().'&uuid='.$donation->getUuid()->toString());
    }

    private function getService(string $name)
    {
        return $this->getContainer()->get($name);
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
