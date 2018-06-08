<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Donation;
use AppBundle\Exception\PayboxPaymentUnsubscriptionException;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\PayboxPaymentUnsubscriptionConfirmationMessage;
use Lexik\Bundle\PayboxBundle\Paybox\System\Cancellation\Request as LexikRequest;

class PayboxPaymentUnsubscription
{
    private $request;
    private $mailer;

    public function __construct(MailerService $mailer, LexikRequest $request)
    {
        $this->mailer = $mailer;
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
        $this->mailer->sendMessage(PayboxPaymentUnsubscriptionConfirmationMessage::create($adherent));
    }
}
