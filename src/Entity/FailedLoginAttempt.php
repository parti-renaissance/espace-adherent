<?php

namespace App\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Security\LoginAttemptSignature;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FailedLoginAttemptRepository")
 *
 * @Algolia\Index(autoIndex=false)
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

    private function __construct(string $signature, array $extra)
    {
        $this->uuid = Uuid::uuid4();
        $this->signature = $signature;
        $this->at = \DateTime::createFromFormat('U', time());
        $this->extra = $extra;
    }

    public static function createFromRequest(Request $request): self
    {
        $attempt = LoginAttemptSignature::createFromRequest($request);

        return new self(
            $attempt->getSignature(),
            [
                'login' => $attempt->getLogin(),
                'ip' => $attempt->getIp(),
                'user_agent' => $request->headers->get('User-Agent'),
            ]
        );
    }

    public function getSignature(): string
    {
        return $this->signature;
    }
}
