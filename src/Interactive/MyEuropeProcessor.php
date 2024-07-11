<?php

namespace App\Interactive;

use App\Entity\MyEuropeChoice;
use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Validator\Recaptcha as AssertRecaptcha;
use App\ValueObject\Genders;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertRecaptcha(groups={"send"})
 */
final class MyEuropeProcessor implements RecaptchaChallengeInterface
{
    use RecaptchaChallengeTrait;

    public const STATE_NEEDS_FRIEND_INFO = 'needs_friend_info';
    public const STATE_NEEDS_FRIEND_CASES = 'needs_friend_cases';
    public const STATE_NEEDS_FRIEND_APPRECIATIONS = 'needs_friend_appreciations';
    public const STATE_SUMMARY = 'summary';
    public const STATE_SENT = 'sent';

    public const STATES = [
        self::STATE_NEEDS_FRIEND_INFO,
        self::STATE_NEEDS_FRIEND_CASES,
        self::STATE_NEEDS_FRIEND_APPRECIATIONS,
        self::STATE_SUMMARY,
        self::STATE_SENT,
    ];

    public const TRANSITION_FILL_INFO = 'fill_info';
    public const TRANSITION_FILL_CASES = 'fill_cases';
    public const TRANSITION_FILL_APPRECIATIONS = 'fill_appreciations';
    public const TRANSITION_SEND = 'send';

    public const TRANSITIONS = [
        self::TRANSITION_FILL_INFO,
        self::TRANSITION_FILL_CASES,
        self::TRANSITION_FILL_APPRECIATIONS,
        self::TRANSITION_SEND,
    ];

    #[Assert\Length(max: 50, groups: ['fill_info'])]
    #[Assert\NotBlank(groups: ['fill_info'])]
    #[Assert\Type('string', groups: ['fill_info'])]
    public $friendFirstName = '';

    #[Assert\NotBlank(groups: ['fill_info'])]
    #[Assert\Range(min: 17, groups: ['fill_info'])]
    #[Assert\Type('integer', groups: ['fill_info'])]
    public $friendAge = 0;

    #[Assert\Choice(callback: [Genders::class, 'all'], groups: ['fill_info'])]
    #[Assert\NotBlank(groups: ['fill_info'])]
    public $friendGender;

    /**
     * @var MyEuropeChoice|null
     */
    #[Assert\Type(MyEuropeChoice::class, groups: ['fill_info'])]
    public $friendPosition;

    /**
     * @Assert\All({
     *     @Assert\Type("App\Entity\MyEuropeChoice")
     * }, groups={"fill_cases"})
     */
    #[Assert\Count(min: 1, max: 2, minMessage: 'interactive.friend_cases.min', maxMessage: 'interactive.friend_cases.max', groups: ['fill_cases'])]
    public $friendCases = [];

    /**
     * @Assert\All({
     *     @Assert\Type("App\Entity\MyEuropeChoice")
     * }, groups={"fill_appreciations"})
     */
    #[Assert\Count(min: 1, max: 2, minMessage: 'interactive.friend_appreciations.min', maxMessage: 'interactive.friend_appreciations.max', groups: ['fill_appreciations'])]
    public $friendAppreciations;

    #[Assert\Length(max: 100, groups: ['send'])]
    #[Assert\NotBlank(groups: ['send'])]
    #[Assert\Type('string', groups: ['send'])]
    public $messageSubject = '';

    #[Assert\NotBlank(groups: ['send'])]
    #[Assert\Type('string', groups: ['send'])]
    public $messageContent = '';

    #[Assert\Length(max: 50, groups: ['send'])]
    #[Assert\NotBlank(groups: ['send'])]
    #[Assert\Type('string', groups: ['send'])]
    public $selfFirstName = '';

    #[Assert\Length(max: 50, groups: ['send'])]
    #[Assert\Type('string', groups: ['send'])]
    public $selfLastName = '';

    #[Assert\Email(groups: ['send'])]
    #[Assert\Length(max: 255, maxMessage: 'common.email.max_length', groups: ['send'])]
    #[Assert\NotBlank(groups: ['send'])]
    public $selfEmail = '';

    #[Assert\Email(groups: ['send'])]
    #[Assert\Length(max: 255, maxMessage: 'common.email.max_length', groups: ['send'])]
    #[Assert\NotBlank(groups: ['send'])]
    public $friendEmail = '';

    /**
     * Handled by the workflow.
     */
    private ?string $marking = null;

    /**
     * @return string[]
     */
    public function getArguments(): array
    {
        foreach (MyEuropeChoice::getStepsOrderForEmail() as $step) {
            switch ($step) {
                case MyEuropeChoice::STEP_FRIEND_CASES:
                    $choices = $this->friendCases;

                    break;
                case MyEuropeChoice::STEP_FRIEND_APPRECIATIONS:
                    $choices = $this->friendAppreciations;

                    break;
                default:
                    // Not handled
                    continue 2;
            }

            foreach ($choices ?? [] as $choice) {
                $arguments[] = $choice->getContent();
            }
        }

        return $arguments ?? [];
    }

    public function defineChoices(Collection $collection): void
    {
        // Ensure the collection is new
        $collection->clear();

        if ($this->friendPosition) {
            $collection->add($this->friendPosition);
        }

        foreach ($this->friendCases as $interest) {
            $collection->add($interest);
        }

        foreach ($this->friendAppreciations as $reason) {
            $collection->add($reason);
        }
    }

    public function refreshChoices(ObjectManager $manager): void
    {
        if ($this->friendPosition) {
            $this->friendPosition = $manager->merge($this->friendPosition);
        }

        $refreshedCases = [];
        foreach ($this->friendCases as $interest) {
            $refreshedCases[] = $manager->merge($interest);
        }
        $this->friendCases = $refreshedCases;

        $refreshedReasons = [];
        foreach ($this->friendAppreciations as $reason) {
            $refreshedReasons[] = $manager->merge($reason);
        }
        $this->friendAppreciations = $refreshedReasons;
    }

    public function getMarking(): ?string
    {
        return $this->marking;
    }

    public function setMarking(?string $marking): void
    {
        $this->marking = $marking;
    }
}
