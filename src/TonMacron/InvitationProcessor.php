<?php

namespace App\TonMacron;

use App\Entity\TonMacronChoice;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\Validator\Constraints as Assert;

final class InvitationProcessor
{
    public const STATE_NEEDS_FRIEND_INFO = 'needs_friend_info';
    public const STATE_NEEDS_FRIEND_PROJECT = 'needs_friend_project';
    public const STATE_NEEDS_FRIEND_INTERESTS = 'needs_friend_interests';
    public const STATE_NEEDS_SELF_REASONS = 'needs_self_reasons';
    public const STATE_SUMMARY = 'summary';
    public const STATE_SENT = 'sent';

    public const STATES = [
        self::STATE_NEEDS_FRIEND_INFO,
        self::STATE_NEEDS_FRIEND_PROJECT,
        self::STATE_NEEDS_FRIEND_INTERESTS,
        self::STATE_NEEDS_SELF_REASONS,
        self::STATE_SUMMARY,
        self::STATE_SENT,
    ];

    public const TRANSITION_FILL_INFO = 'fill_info';
    public const TRANSITION_FILL_PROJECT = 'fill_project';
    public const TRANSITION_FILL_INTERESTS = 'fill_interests';
    public const TRANSITION_FILL_REASONS = 'fill_reasons';
    public const TRANSITION_SEND = 'send';

    public const TRANSITIONS = [
        self::TRANSITION_FILL_INFO,
        self::TRANSITION_FILL_PROJECT,
        self::TRANSITION_FILL_INTERESTS,
        self::TRANSITION_FILL_REASONS,
        self::TRANSITION_SEND,
    ];

    /**
     * @Assert\NotBlank(groups={"fill_info"})
     * @Assert\Type("string", groups={"fill_info"})
     * @Assert\Length(max=50, groups={"fill_info"})
     */
    public $friendFirstName = '';

    /**
     * @Assert\NotBlank(groups={"fill_info"})
     * @Assert\Type("integer", groups={"fill_info"})
     * @Assert\Range(min=17, groups={"fill_info"})
     */
    public $friendAge = 0;

    /**
     * @Assert\NotBlank(groups={"fill_info"})
     * @Assert\Choice(callback={"App\ValueObject\Genders", "all"}, strict=true, groups={"fill_info"})
     */
    public $friendGender;

    /**
     * @var TonMacronChoice|null
     *
     * @Assert\Type("App\Entity\TonMacronChoice", groups={"fill_info"})
     */
    public $friendPosition;

    /**
     * @Assert\NotBlank(groups={"fill_project"})
     * @Assert\Type("App\Entity\TonMacronChoice", groups={"fill_project"})
     */
    public $friendProject;

    /**
     * @Assert\Count(min=2, max=2, exactMessage="ton_macron.invitation.friend_interests.count", groups={"fill_interests"})
     * @Assert\All({
     *     @Assert\Type("App\Entity\TonMacronChoice")
     * }, groups={"fill_interests"})
     */
    public $friendInterests = [];

    /**
     * @Assert\Count(min=2, max=2, exactMessage="ton_macron.invitation.self_reasons.count", groups={"fill_reasons"})
     * @Assert\All({
     *     @Assert\Type("App\Entity\TonMacronChoice")
     * }, groups={"fill_reasons"})
     */
    public $selfReasons;

    /**
     * @Assert\NotBlank(groups={"send"})
     * @Assert\Type("string", groups={"send"})
     * @Assert\Length(max=100, groups={"send"})
     */
    public $messageSubject = '';

    /**
     * @Assert\NotBlank(groups={"send"})
     * @Assert\Type("string", groups={"send"})
     */
    public $messageContent = '';

    /**
     * @Assert\NotBlank(groups={"send"})
     * @Assert\Type("string", groups={"send"})
     * @Assert\Length(max=50, groups={"send"})
     */
    public $selfFirstName = '';

    /**
     * @Assert\NotBlank(groups={"send"})
     * @Assert\Type("string", groups={"send"})
     * @Assert\Length(max=50, groups={"send"})
     */
    public $selfLastName = '';

    /**
     * @Assert\NotBlank(groups={"send"})
     * @Assert\Email(groups={"send"})
     * @Assert\Length(max=255, maxMessage="common.email.max_length", groups={"send"})
     */
    public $selfEmail = '';

    /**
     * @Assert\NotBlank(groups={"send"})
     * @Assert\Email(groups={"send"})
     * @Assert\Length(max=255, maxMessage="common.email.max_length", groups={"send"})
     */
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
        foreach (TonMacronChoice::getStepsOrderForEmail() as $step) {
            switch ($step) {
                case TonMacronChoice::STEP_FRIEND_PROFESSIONAL_POSITION:
                    $choices = [$this->friendPosition];

                    break;
                case TonMacronChoice::STEP_FRIEND_PROJECT:
                    $choices = [$this->friendProject];

                    break;
                case TonMacronChoice::STEP_FRIEND_INTERESTS:
                    $choices = $this->friendInterests;

                    break;
                case TonMacronChoice::STEP_SELF_REASONS:
                    $choices = $this->selfReasons;

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

        if ($this->friendProject) {
            $collection->add($this->friendProject);
        }

        foreach ($this->friendInterests as $interest) {
            $collection->add($interest);
        }

        foreach ($this->selfReasons as $reason) {
            $collection->add($reason);
        }
    }

    public function refreshChoices(ObjectManager $manager): void
    {
        if ($this->friendPosition) {
            $this->friendPosition = $manager->merge($this->friendPosition);
        }

        if ($this->friendProject) {
            $this->friendProject = $manager->merge($this->friendProject);
        }

        $refreshedInterests = [];
        foreach ($this->friendInterests as $interest) {
            $refreshedInterests[] = $manager->merge($interest);
        }
        $this->friendInterests = $refreshedInterests;

        $refreshedReasons = [];
        foreach ($this->selfReasons as $reason) {
            $refreshedReasons[] = $manager->merge($reason);
        }
        $this->selfReasons = $refreshedReasons;
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
