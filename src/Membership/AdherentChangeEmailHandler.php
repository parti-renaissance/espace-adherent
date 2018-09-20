<?php

namespace AppBundle\Membership;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentChangeEmailToken;
use AppBundle\Mail\Transactional\AdherentChangeEmailMail;
use AppBundle\Repository\AdherentChangeEmailTokenRepository;
use Doctrine\Common\Persistence\ObjectManager;
use EnMarche\MailerBundle\MailPost\MailPostInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AdherentChangeEmailHandler
{
    private $mailPost;
    private $manager;
    private $repository;
    private $urlGenerator;
    private $dispatcher;

    public function __construct(
        MailPostInterface $mailPost,
        ObjectManager $manager,
        AdherentChangeEmailTokenRepository $repository,
        UrlGeneratorInterface $urlGenerator,
        EventDispatcherInterface $dispatcher
    ) {
        $this->mailPost = $mailPost;
        $this->manager = $manager;
        $this->repository = $repository;
        $this->urlGenerator = $urlGenerator;
        $this->dispatcher = $dispatcher;
    }

    public function handleRequest(Adherent $adherent, string $newEmailAddress): void
    {
        $token = AdherentChangeEmailToken::generate($adherent);
        $token->setEmail(mb_strtolower($newEmailAddress));

        $this->manager->persist($token);
        $this->manager->flush();

        $this->sendValidationEmail($adherent, $token);

        $this->repository->invalidateOtherActiveToken($adherent, $token);
    }

    public function handleValidationRequest(Adherent $adherent, AdherentChangeEmailToken $token): void
    {
        $adherent->changeEmail($token);
        $this->manager->flush();
        $this->dispatcher->dispatch(UserEvents::USER_UPDATED, new UserEvent($adherent));
    }

    public function sendValidationEmail(Adherent $adherent, AdherentChangeEmailToken $token): void
    {
        $this->mailPost->address(
            AdherentChangeEmailMail::class,
            AdherentChangeEmailMail::createRecipientFor(
                $adherent,
                $this->urlGenerator->generate(
                    'user_validate_new_email',
                    [
                        'adherent_uuid' => $adherent->getUuidAsString(),
                        'change_email_token' => $token->getValue(),
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            )
        );
    }
}
