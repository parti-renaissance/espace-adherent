<?php

namespace AppBundle\Entity;

use AppBundle\Utils\EmojisRemover;
use AppBundle\Validator\Recaptcha as AssertRecaptcha;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Table(name="je_marche_reports")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\JeMarcheReportRepository")
 */
class JeMarcheReport
{
    use EntityTimestampableTrait;

    const TYPE_WALK = 'la-marche';
    const TYPE_DOOR_TO_DOOR = 'porte-a-porte';
    const TYPE_DINNER = 'diner';
    const TYPE_CONVERSATION = 'conversation';
    const TYPE_WORKSHOP = 'atelier';
    const TYPE_ACTION = 'action-qui-me-ressemble';

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(length=30)
     *
     * @Assert\NotBlank
     * @Assert\Choice(callback="getTypes")
     */
    private $type = '';

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(message="common.email.not_blank")
     * @Assert\Email(message="common.email.invalid")
     */
    private $emailAddress = '';

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=11)
     *
     * @Assert\NotBlank(message="jemarche.postal_code.not_blank")
     * @Assert\Length(
     *     min=2,
     *     max=11,
     *     minMessage="jemarche.postal_code.invalid",
     *     maxMessage="jemarche.postal_code.invalid"
     * )
     */
    private $postalCode = '';

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @Assert\All({
     *      @Assert\Email(message="jemarche.email.invalid")
     * })
     */
    private $convinced = [];

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @Assert\All({
     *      @Assert\Email(message="jemarche.email.invalid")
     * })
     */
    private $almostConvinced = [];

    /**
     * @var int|null
     *
     * @ORM\Column(type="smallint", nullable=true)
     *
     * @Assert\GreaterThanOrEqual(value=0, message="jemarche.not_conviced.greater_than_or_equal_0")
     */
    private $notConvinced;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Assert\Length(max=2500, maxMessage="jemarche.reaction.max_2500")
     */
    private $reaction = '';

    /**
     * @var string
     *
     * @Assert\NotBlank(message="common.recaptcha.invalid_message")
     * @AssertRecaptcha
     */
    public $recaptcha = '';

    public static function createWithCaptcha(string $recaptcha)
    {
        $report = new self();
        $report->recaptcha = $recaptcha;

        return $report;
    }

    /**
     * @Assert\Callback
     */
    public function validateOneFieldNotBlank(ExecutionContextInterface $context, $payload)
    {
        if (!$this->notConvinced && !$this->almostConvinced && !$this->convinced) {
            $context->addViolation('Vous devez entrer au moins un contact que vous avez obtenu durant une action.');
        }
    }

    public function __toString()
    {
        return $this->type.' de '.$this->emailAddress;
    }

    public static function getTypes()
    {
        return [
            self::TYPE_WALK,
            self::TYPE_DOOR_TO_DOOR,
            self::TYPE_DINNER,
            self::TYPE_CONVERSATION,
            self::TYPE_WORKSHOP,
            self::TYPE_ACTION,
        ];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type)
    {
        $this->type = $type;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode)
    {
        $this->postalCode = $postalCode;
    }

    public function getConvinced(): array
    {
        return $this->convinced;
    }

    public function getConvincedAsString(): string
    {
        return implode("\n", $this->convinced);
    }

    public function countConvinced(): int
    {
        return count($this->convinced);
    }

    public function setConvinced(array $convinced)
    {
        $this->convinced = $convinced;
    }

    public function getAlmostConvinced(): array
    {
        return $this->almostConvinced;
    }

    public function getAlmostConvincedAsString(): string
    {
        return implode("\n", $this->almostConvinced);
    }

    public function countAlmostConvinced(): int
    {
        return count($this->almostConvinced);
    }

    public function setAlmostConvinced(array $almostConvinced)
    {
        $this->almostConvinced = $almostConvinced;
    }

    public function getNotConvinced(): ?int
    {
        return $this->notConvinced;
    }

    public function setNotConvinced(?int $notConvinced)
    {
        $this->notConvinced = $notConvinced;
    }

    public function getReaction(): string
    {
        return $this->reaction;
    }

    public function setReaction(string $reaction)
    {
        $this->reaction = EmojisRemover::remove($reaction);
    }
}
