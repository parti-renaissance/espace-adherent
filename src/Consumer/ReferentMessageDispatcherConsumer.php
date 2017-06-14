<?php

namespace AppBundle\Consumer;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Projection\ReferentManagedUser;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\ReferentMessage as MailjetMessage;
use AppBundle\Referent\ReferentMessage;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Symfony\Component\Validator\Constraints as Assert;

class ReferentMessageDispatcherConsumer extends AbstractConsumer
{
    protected function configureDataConstraints(): array
    {
        return [
            'referent_uuid' => [new Assert\NotBlank()],
            'filter' => [new Assert\NotBlank()],
            'subject' => [new Assert\NotBlank()],
            'content' => [new Assert\NotBlank()],
        ];
    }

    public function doExecute(array $data): bool
    {
        $logger = $this->getLogger();
        $manager = $this->getDoctrine()->getManager();
        $adherentRepository = $manager->getRepository(Adherent::class);
        $managedUserRepository = $manager->getRepository(ReferentManagedUser::class);
        $mailer = $this->container->get('app.mailjet.campaign_mailer');

        try {
            if (!$referent = $adherentRepository->findByUuid($data['referent_uuid'])) {
                $logger->error('Referent not found', $data);
                $this->writeln($data['referent_uuid'], 'Referent not found, rejecting');

                return true;
            }

            $message = ReferentMessage::createFromArray($referent, $data);
            $this->writeln($data['referent_uuid'], 'Dispatching message from '.$referent->getEmailAddress());

            /** @var IterableResult $results */
            $results = $managedUserRepository->createDispatcherIterator($referent, $message->getFilter());

            $i = 0;
            $count = 0;
            $chunk = [];

            foreach ($results as $result) {
                ++$i;
                ++$count;
                $chunk[] = $result[0];

                if ($i === MailjetService::PAYLOAD_MAXSIZE) {
                    $mailer->sendMessage(MailjetMessage::createFromModel($message, $chunk));
                    $this->writeln($data['referent_uuid'], 'Message from '.$referent->getEmailAddress().' dispatched ('.$count.')');

                    $i = 0;
                    $chunk = [];

                    $manager->clear();
                }
            }

            if (!empty($chunk)) {
                $mailer->sendMessage(MailjetMessage::createFromModel($message, $chunk));
                $this->writeln($data['referent_uuid'], 'Message from '.$referent->getEmailAddress().' dispatched ('.$count.')');
            }

            return true;
        } catch (\Exception $error) {
            $logger->error('Consumer failed', ['exception' => $error]);

            throw $error;
        }
    }
}
