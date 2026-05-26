<?php

declare(strict_types=1);

namespace Tests\App\Controller\Renaissance\Security;

use App\Entity\Adherent;
use App\Entity\PostAddress;
use App\Membership\AdherentFactory;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Tests\App\AbstractRenaissanceWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * Locks the PENDING-contact activation by magic link.
 *
 * A contact captured in the central email registry is created PENDING. It must be able to log in
 * through a magic link, which activates it (ENABLED) and opens a ROLE_USER session — without being
 * a Renaissance member. This behaviour relies on the priority ordering documented in
 * MagicLinkAuthenticationListener::getSubscribedEvents(): a silent regression there would break it.
 */
#[Group('functional')]
#[Group('security')]
class MagicLinkPendingActivationTest extends AbstractRenaissanceWebTestCase
{
    use ControllerTestTrait;

    public function testPendingContactActivatesAndGetsRoleUserButNotMemberAccessViaMagicLink(): void
    {
        $email = 'magic-link-pending-contact@example.test';

        // A PENDING contact, without the `adherent` membership tag.
        $this->createPendingContact($email);

        // check_post_only => true: only a POST to the check route triggers the login_link authenticator.
        $this->client->request(Request::METHOD_POST, $this->createMagicLinkUrl($email));

        // Authenticated: redirected to the post-login target, not to the failure path.
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        self::assertStringNotContainsString(
            '/connexion',
            (string) $this->client->getResponse()->headers->get('location')
        );

        // The PENDING contact has been activated by MagicLinkAuthenticationListener.
        $this->manager->clear();
        $reloaded = $this->getAdherentRepository()->findOneByEmail($email);
        self::assertTrue($reloaded->isEnabled());

        // Surface: ROLE_USER grants the base account space...
        $this->client->request(Request::METHOD_GET, '/parametres/mon-compte');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // ...but not the member-only area guarded by RenaissanceAdherentVoter.
        $this->client->request(Request::METHOD_GET, '/espace-adherent/formations');
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testDisabledAdherentCannotActivateNorLoginViaMagicLink(): void
    {
        $email = 'simple-user-disabled@example.ch';

        $disabled = $this->getAdherentRepository()->findOneByEmail($email);
        self::assertTrue($disabled->isDisabled());

        $this->client->request(Request::METHOD_POST, $this->createMagicLinkUrl($email));

        // Rejected by UserChecker::checkPostAuth: redirected to the failure path, account stays DISABLED.
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        self::assertStringContainsString(
            '/connexion',
            (string) $this->client->getResponse()->headers->get('location')
        );

        $this->manager->clear();
        $reloaded = $this->getAdherentRepository()->findOneByEmail($email);
        self::assertTrue($reloaded->isDisabled());

        // Not authenticated: the base account space is no longer reachable.
        $this->client->request(Request::METHOD_GET, '/parametres/mon-compte');
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
    }

    private function createPendingContact(string $email): Adherent
    {
        /** @var AdherentFactory $factory */
        $factory = $this->get(AdherentFactory::class);

        $contact = $factory->createFromArray([
            'email' => $email,
            'password' => 'dummy-password',
            'first_name' => 'Pending',
            'last_name' => 'Contact',
            'address' => PostAddress::createFrenchAddress('2 rue de la Paix', '75008-75108'),
        ]);

        self::assertTrue($contact->isPending());

        $this->manager->persist($contact);
        $this->manager->flush();

        return $contact;
    }

    private function createMagicLinkUrl(string $email): string
    {
        $adherent = $this->getAdherentRepository()->findOneByEmail($email);

        /** @var LoginLinkHandlerInterface $loginLinkHandler */
        $loginLinkHandler = $this->get(LoginLinkHandlerInterface::class);

        $parts = parse_url($loginLinkHandler->createLoginLink($adherent)->getUrl());

        return $parts['path'].'?'.$parts['query'];
    }
}
