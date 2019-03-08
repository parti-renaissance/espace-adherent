<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="ton_macron_choices", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="ton_macron_choices_uuid_unique", columns="uuid"),
 *     @ORM\UniqueConstraint(name="ton_macron_choices_content_key_unique", columns="content_key")
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TonMacronChoiceRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class TonMacronChoice
{
    use EntityIdentityTrait;
    use EntityCrudTrait;

    const MAIL_INTRODUCTION_KEY = 'S00C01';
    const MAIL_CONCLUSION_KEY = 'S00C02';
    const MALE_KEY = 'S00C03';
    const FEMALE_KEY = 'S00C04';

    const STEP_NOT_VISIBLE = 'ton_macron.not_visible';
    const STEP_FRIEND_PROFESSIONAL_POSITION = 'ton_macron.friend_professional_position';
    const STEP_FRIEND_PROJECT = 'ton_macron.friend_project';
    const STEP_FRIEND_INTERESTS = 'ton_macron.friend_interests';
    const STEP_SELF_REASONS = 'ton_macron.self_reasons';

    const STEPS = [
        self::STEP_NOT_VISIBLE => 0,
        self::STEP_FRIEND_PROFESSIONAL_POSITION => 1,
        self::STEP_FRIEND_PROJECT => 2,
        self::STEP_FRIEND_INTERESTS => 3,
        self::STEP_SELF_REASONS => 4,
    ];

    /**
     * @ORM\Column(type="smallint", length=1, options={"unsigned": true})
     */
    private $step;

    /**
     * @ORM\Column(length=30)
     */
    private $contentKey;

    /**
     * @ORM\Column(length=100)
     */
    private $label;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    public static function getStepsOrderForEmail(): array
    {
        return [
            self::STEP_SELF_REASONS,
            self::STEP_FRIEND_PROFESSIONAL_POSITION,
            self::STEP_FRIEND_INTERESTS,
            self::STEP_FRIEND_PROJECT,
        ];
    }

    public function __construct(
        UuidInterface $uuid = null,
        string $step = null,
        string $contentKey = null,
        string $label = null,
        string $content = null
    ) {
        $this->uuid = $uuid ?: Uuid::uuid4();
        $this->step = $step;
        $this->contentKey = $contentKey;
        $this->label = $label;
        $this->content = $content;
    }

    public function __toString(): string
    {
        return $this->label ?: '';
    }

    public function getStep(): ?string
    {
        return $this->step;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getContentKey(): ?string
    {
        return $this->contentKey;
    }
}
