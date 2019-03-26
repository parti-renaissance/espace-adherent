<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\AssessorOfficeEnum;
use AppBundle\Entity\AssessorRequest;
use AppBundle\Entity\VotePlace;
use PHPUnit\Framework\TestCase;

/**
 * @group assessor
 */
class AssessorRequestTest extends TestCase
{
    /**
     * @dataProvider provideProcessTestCases
     */
    public function testAssessorRequestProcess(
        string $assessorOffice,
        bool $holderOfficeAvailable,
        bool $substituteOfficeAvailable,
        array $availableOffices
    ) {
        $assessorRequet = new AssessorRequest();
        $assessorRequet->setOffice($assessorOffice);

        $votePlace = new VotePlace();

        $assessorRequet->process($votePlace);

        $this->assertEquals($holderOfficeAvailable, $votePlace->isHolderOfficeAvailable());
        $this->assertEquals($substituteOfficeAvailable, $votePlace->isSubstituteOfficeAvailable());
        $this->assertEquals($availableOffices, $votePlace->getAvailableOffices());
        $this->assertEquals(true, $assessorRequet->isProcessed());
        $this->assertEquals(true, $assessorRequet->getProcessedAt() instanceof \DateTime);
        $this->assertEquals(false, null === $assessorRequet->getVotePlace());
    }

    public function provideProcessTestCases(): \Generator
    {
        yield 'After processing an holder to a vote place, we should have only one substitute office available on the vote place' => [
            AssessorOfficeEnum::HOLDER, 0, 1, ['assessor_request.office.substitute.label'],
        ];
        yield 'After processing a substitute to a vote place, we should have only one holder office available on the vote place' => [
            AssessorOfficeEnum::SUBSTITUTE, 1, 0, ['assessor_request.office.holder.label'],
        ];
    }

    /**
     * @dataProvider provideUnprocessTestCases
     */
    public function testAssessorRequestUnprocess(
        string $assessorOffice,
        bool $holderOfficeAvailable,
        bool $substituteOfficeAvailable,
        array $availableOffices
    ) {
        $assessorRequet = new AssessorRequest();
        $assessorRequet->setOffice($assessorOffice);

        $votePlace = new VotePlace();

        $assessorRequet->process($votePlace);
        $assessorRequet->unprocess();

        $this->assertEquals($holderOfficeAvailable, $votePlace->isHolderOfficeAvailable());
        $this->assertEquals($substituteOfficeAvailable, $votePlace->isSubstituteOfficeAvailable());
        $this->assertEquals($availableOffices, $votePlace->getAvailableOffices());
        $this->assertEquals(false, $assessorRequet->isProcessed());
        $this->assertEquals(false, $assessorRequet->getProcessedAt() instanceof \DateTime);
        $this->assertEquals(true, null === $assessorRequet->getVotePlace());
    }

    public function provideUnprocessTestCases(): \Generator
    {
        yield 'After unprocessing an holder to a vote place, we should have both offices available on the vote place' => [
            AssessorOfficeEnum::HOLDER, 1, 1, ['assessor_request.office.holder.label', 'assessor_request.office.substitute.label'],
        ];
        yield 'After unprocessing a substitute to a vote place, we should have both offices available on the vote place' => [
            AssessorOfficeEnum::SUBSTITUTE, 1, 1, ['assessor_request.office.holder.label', 'assessor_request.office.substitute.label'],
        ];
    }
}
