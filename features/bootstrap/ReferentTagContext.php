<?php

use App\Entity\ReferentTag;
use App\Repository\AdherentRepository;
use Behat\MinkExtension\Context\RawMinkContext;

class ReferentTagContext extends RawMinkContext
{
    private $adherentRepository;

    public function __construct(AdherentRepository $adherentRepository)
    {
        $this->adherentRepository = $adherentRepository;
    }

    /**
     * @Then the adherent :email should have the :code referent tag
     */
    public function theAdherentShouldHaveAReferentTagWithCode(string $email, string $code): void
    {
        $adherent = $this->adherentRepository->findOneByEmail($email);

        $tag = $adherent
            ->getReferentTags()
            ->filter(function (ReferentTag $referentTag) use ($code) {
                return $code === $referentTag->getCode();
            })
            ->first()
        ;

        if (!$tag) {
            throw new \Exception("Adherent with email \"$email\" should be tagged with \"$code\" tag.");
        }
    }
}
