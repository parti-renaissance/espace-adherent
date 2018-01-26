<?php

namespace AppBundle\Entity;

use AppBundle\Security\FailedLoginAttemptSignature;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FailedLoginAttemptRepository")
 */
class FailedLoginAttempt
{
    use EntityIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $signature;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $at;

    /**
     * @var array
     *
     * @ORM\Column(type="json")
     */
    private $extra;

    public function __construct(string $signature, array $extra)
    {
        $this->uuid = Uuid::uuid4();
        $this->signature = $signature;
        $this->at = \DateTime::createFromFormat('U', time());
        $this->extra = $extra;
    }

    public static function createFromRequest(Request $request): self
    {
        return new self(
            (new FailedLoginAttemptSignature($request->get('_adherent_email'), $request->getClientIp()))(),
            [
                'login' => $request->get('_adherent_email'),
                'ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('User-Agent'),
            ]
        );
    }
}
