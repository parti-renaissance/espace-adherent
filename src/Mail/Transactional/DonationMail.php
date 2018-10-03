<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\Donation;
use AppBundle\Entity\Transaction;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

class DonationMail extends TransactionalMail
{
    const SUBJECT = 'Merci pour votre engagement';

    public static function createRecipientFor(Donation $donation): RecipientInterface
    {
        return new Recipient($donation->getEmailAddress(), $donation->getFullName());
    }

    public static function createTemplateVarsFrom(Transaction $transaction): array
    {
        $donation = $transaction->getDonation();

        return [
            'target_firstname' => StringCleaner::htmlspecialchars($donation->getFirstName()),
            'year' => (int) $transaction->getPayboxDateTime()->format('Y') + 1,
            'donation_amount' => $transaction->getDonation()->getAmountInEuros(),
        ];
    }
}
