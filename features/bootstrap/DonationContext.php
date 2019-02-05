<?php

use AppBundle\Entity\Donation;
use AppBundle\Repository\DonationRepository;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\MinkExtension\Context\RawMinkContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tests\AppBundle\Test\Payment\PayboxProvider;

class DonationContext extends RawMinkContext
{
    private const DEFAULT_SESSION_NAME = 'default';

    /**
     * @var RestContext
     */
    private $restContext;

    /**
     * @BeforeScenario
     */
    public function before(BeforeScenarioScope $scope)
    {
        $this->restContext = $scope->getEnvironment()->getContext(\RestContext::class);
    }

    /**
     * @When I simulate IPN call with :status code for the last donation of :email
     */
    public function simulateIpnCall(string $status, string $email): void
    {
        $sessionName = $this->getMink()->getDefaultSessionName();
        $this->getMink()->setDefaultSessionName(self::DEFAULT_SESSION_NAME);

        if (!$donation = $this->getDonation($email)) {
            throw new \Exception(sprintf('Donation not found for email %s', $email));
        }

        /** @var PayboxProvider $payboxProvider */
        $payboxProvider = $this->getService(PayboxProvider::class);
        $data = $payboxProvider->prepareCallbackParameters($donation->getUuid()->toString(), $status);

        $preparedData = [['key', 'value']];

        array_walk($data, function ($value, string $key) use (&$preparedData) {
            $preparedData[] = [$key, $value];
        });

        $data = new TableNode($preparedData);
        $this->restContext->iSendARequestToWithParameters('POST', $payboxProvider->getIpnUri(), $data);

        $this->getMink()->setDefaultSessionName($sessionName);
    }

    private function getService(string $name)
    {
        return $this->getContainer()->get($name);
    }

    private function getDonation(string $email): ?Donation
    {
        /** @var DonationRepository $repository */
        $repository = $this->getService(DonationRepository::class);

        return $repository->findOneBy(['emailAddress' => $email], ['createdAt' => 'DESC']);
    }

    private function getContainer(): ContainerInterface
    {
        $driver = $this->getMink()->getSession()->getDriver();

        if (!$driver instanceof BrowserKitDriver) {
            throw new \RuntimeException(sprintf('Driver must be one instance of %s', BrowserKitDriver::class));
        }

        return $driver->getClient()->getContainer();
    }
}
