<?php

namespace App\BesoinDEurope\Inscription;

use App\Address\Address;
use App\Validator\StrictEmail;
use Symfony\Component\Validator\Constraints as Assert;

class InscriptionRequest
{
    /**
     * @Assert\NotBlank
     * @StrictEmail(dnsCheck=false)
     */
    public ?string $email = null;

    /**
     * @Assert\NotBlank
     * @Assert\Choice(callback={"App\ValueObject\Genders", "all"}, message="common.invalid_choice")
     */
    public ?string $civility = null;

    /**
     * @Assert\NotBlank
     * @Assert\Length(
     *     allowEmptyString=true,
     *     min=2,
     *     max=50,
     *     minMessage="common.first_name.min_length",
     *     maxMessage="common.first_name.max_length"
     * )
     */
    public ?string $firstName = null;

    /**
     * @Assert\NotBlank
     * @Assert\Length(
     *     allowEmptyString=true,
     *     min=1,
     *     max=50,
     *     minMessage="common.last_name.min_length",
     *     maxMessage="common.last_name.max_length"
     * )
     */
    public ?string $lastName = null;

    /**
     * @Assert\Valid
     */
    public ?Address $address = null;

    /**
     * @Assert\NotNull(message="Veuillez cocher une réponse")
     */
    public ?bool $partyMembership = null;

    public bool $allowNotifications = false;

    public bool $acceptCgu = false;
    public bool $acceptCgu2 = false;

    public ?string $utmSource = null;
    public ?string $utmCampaign = null;
}
