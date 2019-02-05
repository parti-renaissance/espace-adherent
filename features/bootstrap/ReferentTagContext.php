<?php

use AppBundle\Entity\Adherent;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Doctrine\ORM\EntityManagerInterface;

class ReferentTagContext extends RawMinkContext
{
    use KernelDictionary;

    /**
     * @Then the adherent :email should have the :code referent tag
     */
    public function theAdherentShouldHaveAReferentTagWithCode(string $email, string $code): void
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $em->clear();

        /** @var Adherent $adherent */
        $adherent = $em
            ->getRepository(Adherent::class)
            ->findOneByEmail($email)
        ;

        if (!in_array($code, $adherent->getReferentTagCodes())) {
            throw new \Exception("Adherent with email \"$email\" should be tagged with \"$code\" tag.");
        }
    }
}
