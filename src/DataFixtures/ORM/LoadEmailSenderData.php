<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Email\EmailSender;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadEmailSenderData extends Fixture
{
    public const SENDER_CONTACT_RENAISSANCE = 'email-sender-contact-renaissance';
    public const SENDER_NE_PAS_REPONDRE_RENAISSANCE = 'email-sender-ne-pas-repondre-renaissance';
    public const SENDER_CONTACT_PARTI = 'email-sender-contact-parti';
    public const SENDER_NE_PAS_REPONDRE_PARTI = 'email-sender-ne-pas-repondre-parti';

    public function load(ObjectManager $manager): void
    {
        $senders = [
            self::SENDER_CONTACT_RENAISSANCE => ['Contact Renaissance', 'contact@renaissance.code'],
            self::SENDER_NE_PAS_REPONDRE_RENAISSANCE => ['Ne pas répondre', 'ne-pas-repondre@renaissance.code'],
            self::SENDER_CONTACT_PARTI => ['Contact Parti', 'contact@parti.re'],
            self::SENDER_NE_PAS_REPONDRE_PARTI => ['Ne pas répondre Parti', 'ne-pas-repondre@parti.re'],
        ];

        foreach ($senders as $reference => [$name, $email]) {
            $manager->persist($sender = new EmailSender());
            $sender->name = $name;
            $sender->email = $email;
            $this->addReference($reference, $sender);
        }

        $manager->flush();
    }
}
