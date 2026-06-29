<?php

declare(strict_types=1);

namespace Tests\App\Entity\Projection;

use App\Entity\Projection\ManagedUser;
use App\Mailchimp\Contact\ContactStatusEnum;
use PHPUnit\Framework\TestCase;

class ManagedUserTest extends TestCase
{
    public function testAvailableForResubscribeWhenUnsubscribedAndNotComplained(): void
    {
        $user = $this->unsubscribedUser();

        self::assertTrue($user->isAvailableForResubscribeEmail());
    }

    public function testNotAvailableForResubscribeWhenComplained(): void
    {
        $user = $this->unsubscribedUser();
        $user->emailComplainedAt = new \DateTimeImmutable();

        self::assertFalse($user->isAvailableForResubscribeEmail(), 'a complainer must not be offered a re-engagement');
    }

    public function testNotAvailableForResubscribeWhenStillSubscribed(): void
    {
        $user = $this->unsubscribedUser();
        $user->mailchimpStatus = ContactStatusEnum::SUBSCRIBED;

        self::assertFalse($user->isAvailableForResubscribeEmail());
    }

    private function unsubscribedUser(): ManagedUser
    {
        $user = new ManagedUser(null, 1, 'managed@example.org', '92 bld du Général Leclerc', '92110');
        $user->mailchimpStatus = ContactStatusEnum::UNSUBSCRIBED;

        return $user;
    }
}
