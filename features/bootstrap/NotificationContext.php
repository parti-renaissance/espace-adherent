<?php

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class NotificationContext extends RawMinkContext
{
    private NotificationRepository $notificationRepository;
    private PropertyAccessorInterface $propertyAccessor;

    public function __construct(
        NotificationRepository $notificationRepository,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->notificationRepository = $notificationRepository;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @Given I should have :expectedCount notification(s)
     */
    public function iShouldHaveNotifications(int $expectedCount, array $criteria = [])
    {
        $actualCount = $this->notificationRepository->count($criteria);

        if ($actualCount !== $expectedCount) {
            $this->raiseException(sprintf('I found %d notification(s) instead of %d', $actualCount, $expectedCount));
        }
    }

    /**
     * @Given I should have 1 notification :notificationClass with data:
     */
    public function iShouldHaveNotificationWithData(string $notificationClass, TableNode $data)
    {
        $this->iShouldHaveNotifications(1, ['notificationClass' => $notificationClass]);

        $notification = $this->notificationRepository->findByNotificationClass($notificationClass)[0];

        foreach ($data->getHash() as $row) {
            if (!isset($row['key']) || !isset($row['value'])) {
                throw new \Exception("You must provide a 'key' and 'value' column in your table node.");
            }

            $this->checkNotificationProperty($notification, $row['key'], $row['value']);
        }
    }

    private function checkNotificationProperty(
        Notification $notification,
        string $property,
        string $expectedValue
    ): void {
        $actualValue = $this->propertyAccessor->getValue($notification, $property);

        if ($expectedValue !== $actualValue) {
            $this->raiseException(sprintf('Expected notification with %s "%s", but got "%s" instead.', $property, $expectedValue, $actualValue));
        }
    }

    private function raiseException(string $message): void
    {
        throw new \RuntimeException($message);
    }
}
