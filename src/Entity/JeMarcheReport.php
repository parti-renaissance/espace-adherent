<?php

namespace App\Entity;

use App\Recaptcha\RecaptchaApiClient;
use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Repository\JeMarcheReportRepository;
use App\Validator\Recaptcha as AssertRecaptcha;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[AssertRecaptcha(api: RecaptchaApiClient::NAME)]
#[ORM\Entity(repositoryClass: JeMarcheReportRepository::class)]
#[ORM\Table(name: 'je_marche_reports')]
class JeMarcheReport implements RecaptchaChallengeInterface
{
    use EntityTimestampableTrait;
    use RecaptchaChallengeTrait;

    public const TYPE_KIOSQUE = 'kiosque';
    public const TYPE_WALK = 'la-marche';
    public const TYPE_DOOR_TO_DOOR = 'porte-a-porte';
    public const TYPE_DINNER = 'diner';
    public const TYPE_CONVERSATION = 'conversation';
    public const TYPE_WORKSHOP = 'atelier';
    public const TYPE_ACTION = 'action-qui-me-ressemble';

    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var string
     */
    #[Assert\Choice(callback: 'getTypes')]
    #[Assert\NotBlank]
    #[ORM\Column(length: 30)]
    private $type = '';

    /**
     * @var string
     */
    #[Assert\Email(message: 'common.email.invalid')]
    #[Assert\Length(max: 255, maxMessage: 'common.email.max_length')]
    #[Assert\NotBlank]
    #[ORM\Column]
    private $emailAddress = '';

    /**
     * @var string
     */
    #[Assert\Length(min: 2, max: 11, minMessage: 'jemarche.postal_code.invalid', maxMessage: 'jemarche.postal_code.invalid')]
    #[Assert\NotBlank(message: 'jemarche.postal_code.not_blank')]
    #[ORM\Column(length: 11)]
    private $postalCode = '';

    /**
     * @var array
     */
    #[Assert\All([
        new Assert\Email(message: 'jemarche.email.invalid'),
        new Assert\Length(max: 255, maxMessage: 'common.email.max_length'),
    ])]
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private $convinced = [];

    /**
     * @var array
     */
    #[Assert\All([
        new Assert\Email(message: 'jemarche.email.invalid'),
        new Assert\Length(max: 255, maxMessage: 'common.email.max_length'),
    ])]
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private $almostConvinced = [];

    /**
     * @var int|null
     */
    #[Assert\GreaterThanOrEqual(value: 0, message: 'jemarche.not_conviced.greater_than_or_equal_0')]
    #[Assert\LessThanOrEqual(value: 65535, message: 'jemarche.not_conviced.less_than_or_equal_65000')]
    #[ORM\Column(type: 'smallint', nullable: true, options: ['unsigned' => true])]
    private $notConvinced;

    /**
     * @var string
     */
    #[Assert\Length(max: 2500, maxMessage: 'jemarche.reaction.max_2500')]
    #[ORM\Column(type: 'text', nullable: true)]
    private $reaction = '';

    public static function createWithCaptcha(string $recaptcha): self
    {
        $report = new self();
        $report->setRecaptcha($recaptcha);

        return $report;
    }

    #[Assert\Callback]
    public function validateOneFieldNotBlank(ExecutionContextInterface $context): void
    {
        if (!$this->notConvinced && !$this->almostConvinced && !$this->convinced) {
            $context->addViolation('Vous devez entrer au moins un contact que vous avez obtenu durant une action.');
        }
    }

    public function __toString()
    {
        return $this->type.' de '.$this->emailAddress;
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_KIOSQUE,
            self::TYPE_WALK,
            self::TYPE_DOOR_TO_DOOR,
            self::TYPE_DINNER,
            self::TYPE_CONVERSATION,
            self::TYPE_WORKSHOP,
            self::TYPE_ACTION,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getConvinced(): array
    {
        return $this->convinced;
    }

    public function getConvincedList(string $glue = '|'): string
    {
        return implode($glue, (array) $this->convinced);
    }

    public function getConvincedAsString(): string
    {
        return implode("\n", $this->convinced);
    }

    public function countConvinced(): int
    {
        return \count($this->convinced);
    }

    public function setConvinced(array $convinced): void
    {
        $this->convinced = $convinced;
    }

    public function getAlmostConvinced(): array
    {
        return $this->almostConvinced;
    }

    public function getAlmostConvincedList(string $glue = '|'): string
    {
        return implode($glue, (array) $this->almostConvinced);
    }

    public function getAlmostConvincedAsString(): string
    {
        return implode("\n", $this->almostConvinced);
    }

    public function countAlmostConvinced(): int
    {
        return \count($this->almostConvinced);
    }

    public function setAlmostConvinced(array $almostConvinced): void
    {
        $this->almostConvinced = $almostConvinced;
    }

    public function getNotConvinced(): ?int
    {
        return $this->notConvinced;
    }

    public function setNotConvinced(?int $notConvinced): void
    {
        $this->notConvinced = $notConvinced;
    }

    public function getReaction(): ?string
    {
        return $this->reaction;
    }

    public function setReaction(?string $reaction): void
    {
        $this->reaction = $reaction;
    }
}
