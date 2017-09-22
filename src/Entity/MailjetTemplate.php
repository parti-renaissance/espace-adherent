<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Mailer\Model\EmailTemplate;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="mailjet_templates")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class MailjetTemplate extends EmailTemplate
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    public function __construct(UuidInterface $uuid, string $messageClass, string $senderEmail, string $senderName)
    {
        $this->uuid = $uuid;

        parent::__construct($messageClass, $senderEmail, $senderName);
    }
}
