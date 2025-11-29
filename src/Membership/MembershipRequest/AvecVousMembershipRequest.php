<?php

declare(strict_types=1);

namespace App\Membership\MembershipRequest;

use App\Address\Address;
use App\Entity\Contact;
use App\Membership\MembershipSourceEnum;
use libphonenumber\PhoneNumber;

class AvecVousMembershipRequest extends AbstractMembershipRequest
{
    public ?string $lastName = null;

    public ?\DateTimeInterface $birthdate = null;

    public ?PhoneNumber $phone = null;

    public ?Address $address = null;

    final public function getSource(): string
    {
        return MembershipSourceEnum::AVECVOUS;
    }

    public static function createFromContact(Contact $contact): self
    {
        $membershipRequest = new self();
        $membershipRequest->firstName = $contact->getFirstName();
        $membershipRequest->lastName = $contact->getLastName();
        $membershipRequest->emailAddress = $contact->getEmailAddress();
        $membershipRequest->cguAccepted = $contact->isCguAccepted();
        $membershipRequest->allowEmailNotifications = $contact->isMailContact();
        $membershipRequest->allowMobileNotifications = $contact->isPhoneContact();
        $membershipRequest->birthdate = $contact->getBirthdate();
        $membershipRequest->phone = $contact->getPhone();
        $membershipRequest->address = Address::createFromAddress($contact->getPostAddress());

        return $membershipRequest;
    }
}
