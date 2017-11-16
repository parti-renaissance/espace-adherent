<?php

namespace Tests\AppBundle\Referent;

use AppBundle\Referent\ManagedUsersFilter;
use PHPUnit\Framework\TestCase;

class ManagedUsersFilterTest extends TestCase
{
    /**
     * @dataProvider dataProviderCreateFromArray
     */
    public function testCreateFromArray(array $data, array $expected): void
    {
        $filter = ManagedUsersFilter::createFromArray($data);

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

    public function dataProviderCreateFromArray(): array
    {
        return [
            [
                [
                    'n' => false,
                    'anc' => true,
                    'aic' => false,
                    'h' => true,
                    's' => false,
                    'ac' => '06330',
                    'city' => '',
                    'id' => '1234',
                    'o' => 10,
                ],
                [
                    'includeNewsletter' => false,
                    'includeAdherentsNoCommittee' => true,
                    'includeAdherentsInCommittee' => false,
                    'includeHosts' => true,
                    'includeSupervisors' => false,
                    'queryAreaCode' => '06330',
                    'queryCity' => '',
                    'queryId' => '1234',
                    'offset' => 10,
                    'token' => '',
                ],
            ],
            // test default values
            [
                [],
                [
                    'includeNewsletter' => true,
                    'includeAdherentsNoCommittee' => true,
                    'includeAdherentsInCommittee' => true,
                    'includeHosts' => true,
                    'includeSupervisors' => true,
                    'queryAreaCode' => '',
                    'queryCity' => '',
                    'queryId' => '',
                    'offset' => 0,
                    'token' => '',
                ],
            ],
        ];
    }
}
