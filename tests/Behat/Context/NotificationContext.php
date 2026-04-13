<?php

declare(strict_types=1);

namespace Tests\App\Behat\Context;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Coduo\PHPMatcher\PHPUnit\PHPMatcherAssertions;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class NotificationContext extends RawMinkContext
{
    use PHPMatcherAssertions;

    private NotificationRepository $notificationRepository;
    private PropertyAccessorInterface $propertyAccessor;

    public function __construct(
        NotificationRepository $notificationRepository,
        PropertyAccessorInterface $propertyAccessor,
    ) {
        $this->notificationRepository = $notificationRepository;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @Given I should have :expectedCount notification(s)
     */
    public function iShouldHaveNotifications(int $expectedCount, array $criteria = []): void
    {
        $actualCount = $this->countNonFixtureNotifications($criteria);

        if ($actualCount !== $expectedCount) {
            $this->raiseException(\sprintf('I found %d notification(s) instead of %d', $actualCount, $expectedCount));
        }
    }

    /**
     * @Given I should have 1 notification :notificationClass with data:
     */
    public function iShouldHaveNotificationWithData(string $notificationClass, TableNode $data): void
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

    /**
     * @Then the notification :notificationClass should target at least :minCount push tokens
     */
    public function theNotificationShouldTargetAtLeastTokens(string $notificationClass, int $minCount): void
    {
        $totalTokens = $this->countTotalTokens($notificationClass);

        if ($totalTokens < $minCount) {
            $this->raiseException(\sprintf(
                'Notification "%s" targets %d token(s), expected at least %d',
                $notificationClass,
                $totalTokens,
                $minCount
            ));
        }
    }

    /**
     * @Then the notification :notificationClass should target less than :maxCount push tokens
     */
    public function theNotificationShouldTargetLessThanTokens(string $notificationClass, int $maxCount): void
    {
        $totalTokens = $this->countTotalTokens($notificationClass);

        if ($totalTokens >= $maxCount) {
            $this->raiseException(\sprintf(
                'Notification "%s" targets %d token(s), expected less than %d',
                $notificationClass,
                $totalTokens,
                $maxCount
            ));
        }
    }

    /**
     * @Then the notification :notificationClass should target strictly less tokens than :otherClass
     */
    public function theNotificationShouldTargetStrictlyLessTokensThan(string $notificationClass, string $otherClass): void
    {
        $count = $this->countTotalTokens($notificationClass);
        $otherCount = $this->countTotalTokens($otherClass);

        if ($count >= $otherCount) {
            $this->raiseException(\sprintf(
                'Notification "%s" targets %d token(s), expected strictly less than "%s" which targets %d',
                $notificationClass,
                $count,
                $otherClass,
                $otherCount
            ));
        }
    }

    /**
     * @Then the notification :notificationClass should not include dead tokens
     */
    public function theNotificationShouldNotIncludeDeadTokens(string $notificationClass): void
    {
        $notifications = $this->notificationRepository->findByNotificationClass($notificationClass);

        foreach ($notifications as $notification) {
            if (\in_array('token-dead-device', $notification->getTokens() ?? [], true)) {
                $this->raiseException(\sprintf(
                    'Notification "%s" includes dead token "token-dead-device"',
                    $notificationClass
                ));
            }
        }
    }

    private function countTotalTokens(string $notificationClass): int
    {
        $notifications = $this->notificationRepository->findByNotificationClass($notificationClass);

        if (empty($notifications)) {
            $this->raiseException(\sprintf('No notification "%s" found', $notificationClass));
        }

        $totalTokens = 0;
        foreach ($notifications as $notification) {
            $totalTokens += $notification->getTokensCount();
        }

        return $totalTokens;
    }

    private function checkNotificationProperty(
        Notification $notification,
        string $property,
        string $expectedValue,
    ): void {
        $actualValue = $this->propertyAccessor->getValue($notification, $property);

        $this->assertMatchesPattern($expectedValue, \is_array($actualValue) ? json_encode($actualValue) : $actualValue);
    }

    private function countNonFixtureNotifications(array $criteria): int
    {
        $qb = $this->notificationRepository->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->andWhere('n.notificationClass NOT LIKE :fixture')
            ->setParameter('fixture', '%Fixture')
        ;

        foreach ($criteria as $field => $value) {
            $qb->andWhere(\sprintf('n.%s = :%s', $field, $field))
                ->setParameter($field, $value)
            ;
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function raiseException(string $message): void
    {
        throw new \RuntimeException($message);
    }
}
