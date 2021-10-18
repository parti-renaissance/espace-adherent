<?php

use App\Repository\NotificationRepository;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;

class NotificationContext extends RawMinkContext
{
    private NotificationRepository $notificationRepository;

    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * @Given I should have :expectedCount notification(s)
     */
    public function iShouldHaveNotifications(int $expectedCount)
    {
        $actualCount = $this->notificationRepository->count([]);

        if ($actualCount !== $expectedCount) {
            throw new \RuntimeException(sprintf('I found %d notification(s) instead of %d', $actualCount, $expectedCount));
        }
    }

    /**
     * @Given I should have 1 notification :notificationClass with data:
     */
    public function iShouldHaveNotificationWithData(string $notificationClass, TableNode $data)
    {
        $notifications = $this->notificationRepository->findByNotificationClass($notificationClass);

        if (1 !== \count($notifications)) {
            throw new \RuntimeException(sprintf('I found %s notification(s) instead of 1', \count($notifications)));
        }

        $notification = reset($notifications);

        foreach ($data->getHash() as $row) {
            if (!isset($row['key']) || !isset($row['value'])) {
                throw new \Exception("You must provide a 'key' and 'value' column in your table node.");
            }

            switch ($row['key']) {
                case 'topic':
                    if ($row['value'] !== $notification->getTopic()) {
                        throw new \RuntimeException(sprintf('Expected notification with topic "%s", but got "%s" instead.', $row['value'], $notification->getTopic()));
                    }

                    break;
                case 'title':
                    if ($row['value'] !== $notification->getTitle()) {
                        throw new \RuntimeException(sprintf('Expected notification with title "%s", but got "%s" instead.', $row['value'], $notification->getTitle()));
                    }

                    break;
                case 'body':
                    if ($row['value'] !== $notification->getBody()) {
                        throw new \RuntimeException(sprintf('Expected notification with body "%s", but got "%s" instead.', $row['value'], $notification->getBody()));
                    }

                    break;
                default:
                    throw new \RuntimeException('Unhandled "%s" property to check for notification.');
            }
        }
    }
}
