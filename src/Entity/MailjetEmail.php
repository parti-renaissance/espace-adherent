<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Mailer\Model\Email;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="mailjet_emails")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MailjetEmailRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class MailjetEmail extends Email
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    public function __construct(UuidInterface $uuid, string $messageClass, string $sender, array $recipients, string $requestPayload)
    {
        $this->uuid = $uuid;

        parent::__construct($messageClass, $sender, $recipients, $requestPayload);
    }
}
