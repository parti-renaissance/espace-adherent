<?php

namespace Tests\AppBundle\BoardMember;

use AppBundle\BoardMember\BoardMemberFilter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class BoardMemberFilterTest extends TestCase
{
    /**
     * @dataProvider dataProviderCreateFromArray
     */
    public function testCreateFromArray(array $data, array $expected): void
    {
        $this->assertExpectedBoardMemberFilter($expected, BoardMemberFilter::createFromArray($data));
    }

    /**
     * @dataProvider dataProviderHandleRequest
     */
    public function testHandleRequest(array $data, array $expected): void
    {
        $query = $this->createMock(ParameterBag::class);
        $query->method('count')->willReturn(count($data));

        $query->method('get')->will($this->returnValueMap([
            ['g', '', $data['g'] ?? ''],
            ['f', '', $data['f'] ?? ''],
            ['l', '', $data['l'] ?? ''],
            ['p', '', $data['p'] ?? ''],
            ['a', [], $data['a'] ?? []],
            ['r', [], $data['r'] ?? []],
            ['t', '', $data['t'] ?? ''],
        ]));
        $query->method('getInt')->will($this->returnValueMap([
            ['amin', 0, $data['amin'] ?? 0],
            ['amax', 0, $data['amax'] ?? 0],
            ['o', 0, $data['o'] ?? 0],
        ]));

        $request = $this->createMock(Request::class);
        $request->query = $query;

        $filter = new BoardMemberFilter();
        $filter->handleRequest($request);

        $this->assertExpectedBoardMemberFilter($expected, $filter);
    }

    public function dataProviderCreateFromArray(): array
    {
        return [
            [
                [
                    'g' => 'male',
                    'amin' => 23,
                    'amax' => 33,
                    'f' => 'Prénom',
                    'l' => 'Nom',
                    'p' => '06330, 92',
                    'a' => ['abroad'],
                    'r' => ['referent'],
                    'o' => 10,
                    'token' => 'b90a23429136d0dd',
                ],
                [
                    'gender' => 'male',
                    'ageMinimum' => 23,
                    'ageMaximum' => 33,
                    'firstName' => 'Prénom',
                    'lastName' => 'Nom',
                    'postalCode' => '06330, 92',
                    'areas' => ['abroad'],
                    'roles' => ['referent'],
                    'offset' => 10,
                    'token' => '',
                ],
            ],
            // test default values
            [
                [],
                [
                    'gender' => '',
                    'ageMinimum' => 0,
                    'ageMaximum' => 0,
                    'firstName' => '',
                    'lastName' => '',
                    'postalCode' => '',
                    'areas' => [],
                    'roles' => [],
                    'offset' => 0,
                    'token' => '',
                ],
            ],
        ];
    }

    public function dataProviderHandleRequest(): array
    {
        return [
            [
                [
                    'g' => 'male',
                    'amin' => 23,
                    'amax' => 33,
                    'f' => 'Prénom',
                    'l' => 'Nom',
                    'p' => '06330, 92',
                    'a' => ['abroad'],
                    'r' => ['referent'],
                    'o' => 10,
                    't' => 'b90a23429136d0dd',
                ],
                [
                    'gender' => 'male',
                    'ageMinimum' => 23,
                    'ageMaximum' => 33,
                    'firstName' => 'Prénom',
                    'lastName' => 'Nom',
                    'postalCode' => '06330, 92',
                    'areas' => ['abroad'],
                    'roles' => ['referent'],
                    'offset' => 10,
                    'token' => 'b90a23429136d0dd',
                ],
            ],
            // test default values
            [
                [],
                [
                    'gender' => '',
                    'ageMinimum' => 0,
                    'ageMaximum' => 0,
                    'firstName' => '',
                    'lastName' => '',
                    'postalCode' => '',
                    'areas' => [],
                    'roles' => [],
                    'offset' => 0,
                    'token' => '',
                ],
            ],
        ];
    }

    private function assertExpectedBoardMemberFilter(array $expected, BoardMemberFilter $filter): void
    {
        $this->assertSame($expected['gender'], $filter->getQueryGender());
        $this->assertSame($expected['ageMinimum'], $filter->getQueryAgeMinimum());
        $this->assertSame($expected['ageMaximum'], $filter->getQueryAgeMaximum());
        $this->assertSame($expected['firstName'], $filter->getQueryFirstName());
        $this->assertSame($expected['lastName'], $filter->getQueryLastName());
        $this->assertSame($expected['postalCode'], $filter->getQueryPostalCode());
        $this->assertSame($expected['areas'], $filter->getQueryAreas());
        $this->assertSame($expected['roles'], $filter->getQueryRoles());
        $this->assertSame($expected['offset'], $filter->getOffset());
        $this->assertSame($expected['token'], $filter->getToken());
    }
}
