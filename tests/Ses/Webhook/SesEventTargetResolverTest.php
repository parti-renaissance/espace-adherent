<?php

declare(strict_types=1);

namespace Tests\App\Ses\Webhook;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Membership\ActivityPositionsEnum;
use App\Scope\ScopeEnum;
use App\Ses\Webhook\SesEventTarget;
use App\Ses\Webhook\SesEventTargetResolver;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Uid\Uuid;
use Tests\App\AbstractKernelTestCase;

/**
 * Real wiring of the shared resolver: it turns the (campaign_uuid, adherent_uuid) tag pair into the internal
 * (messageId, adherentId) target through the actual repositories, gated by AdherentMessage::isSent(), and
 * degrades to null on any unresolvable input.
 */
#[Group('functional')]
class SesEventTargetResolverTest extends AbstractKernelTestCase
{
    private int $seq = 0;

    private SesEventTargetResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = self::getContainer()->get(SesEventTargetResolver::class);
    }

    protected function tearDown(): void
    {
        $this->seq = 0;

        parent::tearDown();
    }

    public function testResolvesSentMessageAndAdherentToTarget(): void
    {
        $recipient = $this->persistAdherent();
        $message = $this->persistMessage(true);

        $target = $this->resolver->resolve($message->getUuid(), $recipient->getUuid());

        self::assertInstanceOf(SesEventTarget::class, $target);
        self::assertSame((int) $message->getId(), $target->messageId);
        self::assertSame($recipient->getId(), $target->adherentId);
    }

    public function testReturnsNullWhenAdherentIsUnknown(): void
    {
        $message = $this->persistMessage(true);

        self::assertNull($this->resolver->resolve($message->getUuid(), Uuid::v4()));
    }

    public function testReturnsNullWhenMessageIsUnknown(): void
    {
        $recipient = $this->persistAdherent();

        self::assertNull($this->resolver->resolve(Uuid::v4(), $recipient->getUuid()));
    }

    public function testReturnsNullWhenMessageIsNotSent(): void
    {
        $recipient = $this->persistAdherent();
        $message = $this->persistMessage(false);

        self::assertNull($this->resolver->resolve($message->getUuid(), $recipient->getUuid()));
    }

    private function persistMessage(bool $sent): AdherentMessage
    {
        $author = $this->persistAdherent();

        $message = new AdherentMessage(null, $author);
        $message->setSubject('Lettre de campagne');
        $message->setContent('<p>Bonjour</p>');
        $message->setInstanceScope(ScopeEnum::NATIONAL);
        if ($sent) {
            $message->markAsSent();
        }

        $this->manager->persist($message);
        $this->manager->flush();

        return $message;
    }

    private function persistAdherent(): Adherent
    {
        $seq = ++$this->seq;
        $email = \sprintf('ses-resolver-%d@test.dev', $seq);

        $phone = new PhoneNumber();
        $phone->setCountryCode(33);
        $phone->setNationalNumber('140998211');

        $adherent = Adherent::create(
            Adherent::createUuid($email),
            \sprintf('SES-R-%d', $seq),
            $email,
            'super-password',
            'female',
            'Sesresolver',
            'Martin',
            new \DateTime('1990-12-12'),
            ActivityPositionsEnum::STUDENT,
            $this->createPostAddress('92 bld du Général Leclerc', '92110-92024'),
            $phone,
            status: Adherent::ENABLED,
        );

        $this->manager->persist($adherent);
        $this->manager->flush();

        return $adherent;
    }
}
