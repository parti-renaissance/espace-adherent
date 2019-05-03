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

        $this->assertSame($expected['includeAdherentsNoCommittee'], $filter->includeAdherentsNoCommittee());
        $this->assertSame($expected['includeAdherentsInCommittee'], $filter->includeAdherentsInCommittee());
        $this->assertSame($expected['includeHosts'], $filter->includeHosts());
        $this->assertSame($expected['includeSupervisors'], $filter->includeSupervisors());
        $this->assertSame($expected['queryZone'], $filter->getQueryZone());
        $this->assertSame($expected['queryAreaCode'], $filter->getQueryAreaCode());
        $this->assertSame($expected['queryCity'], $filter->getQueryCity());
        $this->assertSame($expected['queryId'], $filter->getQueryId());
        $this->assertSame($expected['offset'], $filter->getOffset());
        $this->assertSame($expected['token'], $filter->getToken());
        $this->assertSame($expected['firstname'], $filter->getQueryFirstName());
        $this->assertSame($expected['lastname'], $filter->getQueryLastName());
        $this->assertSame($expected['ageMinimum'], $filter->getQueryAgeMinimum());
        $this->assertSame($expected['ageMaximum'], $filter->getQueryAgeMaximum());
        $this->assertTrue($filter->includeCitizenProject());
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
                    true,
                    false,
                    true,
                    false,
                    'firstname',
                    'lastname',
                    '06',
                    '06330',
                    '',
                    '1234',
                    'male',
                    30,
                    40,
                    true,
                    ['sport']
                ),
                [
                    'includeAdherentsNoCommittee' => true,
                    'includeAdherentsInCommittee' => false,
                    'includeHosts' => true,
                    'includeSupervisors' => false,
                    'queryZone' => '06',
                    'queryAreaCode' => '06330',
                    'queryCity' => '',
                    'queryId' => '1234',
                    'offset' => 0,
                    'token' => null,
                    'includeCP' => true,
                    'interests' => ['sport'],
                    'male',
                    'firstname' => 'firstname',
                    'lastname' => 'lastname',
                    'ageMinimum' => 30,
                    'ageMaximum' => 40,
                ],
            ],
        ];
    }
}
