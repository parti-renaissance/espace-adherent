<?php

declare(strict_types=1);

namespace App\Donation\Paybox;

use App\Entity\Adherent;
use App\Entity\Donation;
use App\Exception\PayboxPaymentUnsubscriptionException;
use App\Mailer\MailerService;
use App\Mailer\Message\DonationUnsubscriptionConfirmationMessage;
use Lexik\Bundle\PayboxBundle\Paybox\System\Cancellation\Request as LexikRequest;

class PayboxPaymentUnsubscription
{
    private $request;
    private $mailer;

    public function __construct(MailerService $transactionalMailer, LexikRequest $request)
    {
        $this->mailer = $transactionalMailer;
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
            throw new PayboxPaymentUnsubscriptionException((int) $result['ERREUR']);
        }

        $donation->stopSubscription();
    }

    public function sendConfirmationMessage(Donation $donation, Adherent $adherent): void
    {
        $this->mailer->sendMessage(DonationUnsubscriptionConfirmationMessage::create($adherent, $donation));
    }
}
