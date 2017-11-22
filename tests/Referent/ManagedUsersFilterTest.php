<?php

namespace Tests\AppBundle\Referent;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ReferentManagedUsersMessage;
use AppBundle\Referent\ManagedUsersFilter;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class ManagedUsersFilterTest extends TestCase
{
    /**
     * @dataProvider dataProviderCreateFromMessage
     */
    public function testCreateFromMessage(ReferentManagedUsersMessage $message, array $expected): void
    {
        $filter = ManagedUsersFilter::createFromMessage($message);

        $this->assertSame($expected['includeNewsletter'], $filter->includeNewsletter());
        $this->assertSame($expected['includeAdherentsNoCommittee'], $filter->includeAdherentsNoCommittee());
        $this->assertSame($expected['includeAdherentsInCommittee'], $filter->includeAdherentsInCommittee());
        $this->assertSame($expected['includeHosts'], $filter->includeHosts());
        $this->assertSame($expected['includeSupervisors'], $filter->includeSupervisors());
        $this->assertSame($expected['queryAreaCode'], $filter->getQueryAreaCode());
        $this->assertSame($expected['queryCity'], $filter->getQueryCity());
        $this->assertSame($expected['queryId'], $filter->getQueryId());
        $this->assertSame($expected['offset'], $filter->getOffset());
        $this->assertSame($expected['token'], $filter->getToken());
    }

    public function dataProviderCreateFromMessage(): array
    {
        return [
            [
                new ReferentManagedUsersMessage(
                    Uuid::uuid4(),
                    $this->createMock(Adherent::class),
                    'Random subject',
                    'Here is the mail content',
                    false,
                    true,
                    false,
                    true,
                    false,
                    '06330',
                    '',
                    '1234'
                ),
                [
                    'includeNewsletter' => false,
                    'includeAdherentsNoCommittee' => true,
                    'includeAdherentsInCommittee' => false,
                    'includeHosts' => true,
                    'includeSupervisors' => false,
                    'queryAreaCode' => '06330',
                    'queryCity' => '',
                    'queryId' => '1234',
                    'offset' => 0,
                    'token' => '',
                ],
            ],
        ];
    }
}
