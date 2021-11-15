<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

trait EntityPersonNameTrait
{
    /**
     * @ORM\Column(length=50)
     *
     * @SymfonySerializer\Groups({
     *     "user_profile",
     *     "export",
     *     "idea_list_read",
     *     "idea_read",
     *     "idea_thread_list_read",
     *     "idea_thread_comment_read",
     *     "idea_vote_read",
     *     "cause_read",
     *     "profile_read",
     *     "event_read",
     *     "event_list_read",
     *     "adherent_autocomplete",
     *     "phoning_campaign_history_read_list",
     * })
     *
     * @JMS\Groups({"adherent_change_diff"})
     * @JMS\SerializedName("firstName")
     */
    private $firstName = '';

    /**
     * @ORM\Column(length=50)
     *
     * @SymfonySerializer\Groups({
     *     "user_profile",
     *     "idea_list_read",
     *     "idea_read",
     *     "idea_thread_list_read",
     *     "idea_thread_comment_read",
     *     "idea_vote_read",
     *     "profile_read",
     *     "cause_read",
     *     "event_read",
     *     "event_list_read",
     *     "adherent_autocomplete",
     *     "phoning_campaign_history_read_list"
     * })
     *
     * @JMS\Groups({"adherent_change_diff"})
     * @JMS\SerializedName("lastName")
     */
    private $lastName = '';

    public function __toString(): string
    {
        return trim($this->getFullName());
    }

    /**
     * @SymfonySerializer\Groups({"api_candidacy_read"})
     */
    public function getFullName(): string
    {
        return $this->firstName.' '.$this->lastName;
    }

    public function getPartialName(): string
    {
        return $this->firstName.' '.$this->getLastNameInitial();
    }

    public function getFirstName(): string
    {
        return (string) $this->firstName;
    }

    public function getLastName(): string
    {
        return (string) $this->lastName;
    }

    /**
     * @SymfonySerializer\Groups({"export", "cause_read"})
     */
    public function getLastNameInitial(bool $padWithDot = true): string
    {
        $normalized = self::normalize($this->lastName);

        $initial = strtoupper($normalized[0]);

        if ($padWithDot) {
            $initial .= '.';
        }

        return $initial;
    }

    public function getFirstNameInitial(): string
    {
        $normalized = self::normalize($this->firstName);

        return mb_strtoupper(mb_substr($normalized, 0, 1));
    }

    /**
     * @SymfonySerializer\Groups({"api_candidacy_read"})
     */
    public function getInitials(): string
    {
        return $this->getFirstNameInitial().$this->getLastNameInitial(false);
    }

    private static function normalize(string $name): string
    {
        return preg_replace('/[^a-z]+/', '', strtolower($name));
    }
}
