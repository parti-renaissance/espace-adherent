<?php

use AppBundle\Entity\CitizenAction;
use AppBundle\Repository\CitizenActionRepository;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

class CitizenProjectContext extends RawMinkContext
{
    use KernelDictionary;

    /**
     * @When I am viewing the citizen action :name
     */
    public function iAmViewingCitizenAction(string $name): void
    {
        /* @var $citizenAction CitizenAction */
        if (!$citizenAction = $this->getCitizenActionRepository()->findOneByName($name)) {
            throw new \Exception(sprintf('Citizen action %s not found', $name));
        }

        $this->visitPath('/action-citoyenne/'.$citizenAction->getSlug());
    }

    protected function getCitizenActionRepository(): CitizenActionRepository
    {
        return $this->getContainer()->get('doctrine')->getRepository(CitizenAction::class);
    }
}
