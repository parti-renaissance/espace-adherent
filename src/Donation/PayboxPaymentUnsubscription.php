<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Donation;
use AppBundle\Exception\PayboxPaymentUnsubscriptionException;
use AppBundle\Mail\Transactional\PayboxPaymentUnsubscriptionConfirmationMail;
use EnMarche\MailerBundle\MailPost\MailPostInterface;
use Lexik\Bundle\PayboxBundle\Paybox\System\Cancellation\Request as LexikRequest;

class PayboxPaymentUnsubscription
{
    private $request;
    private $mailPost;

    public function __construct(MailPostInterface $mailPost, LexikRequest $request)
    {
        $this->mailPost = $mailPost;
        $this->request = $request;
    }

    /**
     * @throws PayboxPaymentUnsubscriptionException
     */
    public function unsubscribe(Donation $donation): void
    {
        $result = [];
        parse_str($this->request->cancel($donation->getPayboxOrderRef()), $result);

        if ('OK' !== $result['ACQ']) {
            throw new PayboxPaymentUnsubscriptionException($result['ERREUR']);
        }

        $donation->stopSubscription();
    }

    public function sendConfirmationMessage(Adherent $adherent): void
    {
        $this->mailPost->address(
            PayboxPaymentUnsubscriptionConfirmationMail::class,
            PayboxPaymentUnsubscriptionConfirmationMail::createRecipient($adherent),
            null,
            [],
            PayboxPaymentUnsubscriptionConfirmationMail::SUBJECT
        );
    }
}
