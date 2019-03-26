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
        string $availableOffices
    ) {
        $assessorRequet = new AssessorRequest();
        $assessorRequet->setOffice($assessorOffice);

        $votePlace = new VotePlace();

        $assessorRequet->process($votePlace);

        $this->assertEquals($holderOfficeAvailable, $votePlace->isHolderOfficeAvailable());
        $this->assertEquals($substituteOfficeAvailable, $votePlace->isSubstitudeOfficeAvailable());
        $this->assertEquals($availableOffices, $votePlace->getAvailableOfficesAsString());
        $this->assertEquals(true, $assessorRequet->isProcessed());
        $this->assertEquals(true, $assessorRequet->getProcessedAt() instanceof \DateTime);
        $this->assertEquals(false, null === $assessorRequet->getVotePlace());
    }

    public function provideProcessTestCases(): \Generator
    {
        yield 'After processing an holder to a vote place, we should have only one substitute office available on the vote place' => [
            AssessorOfficeEnum::HOLDER, 0, 1, 'Suppléant',
        ];
        yield 'After processing a substitude to a vote place, we should have only one holder office available on the vote place' => [
            AssessorOfficeEnum::SUBSTITUTE, 1, 0, 'Titulaire',
        ];
    }

    /**
     * @dataProvider provideUnprocessTestCases
     */
    public function testAssessorRequestUnprocess(
        string $assessorOffice,
        bool $holderOfficeAvailable,
        bool $substituteOfficeAvailable,
        string $availableOffices
    ) {
        $assessorRequet = new AssessorRequest();
        $assessorRequet->setOffice($assessorOffice);

        $votePlace = new VotePlace();

        $assessorRequet->process($votePlace);
        $assessorRequet->unprocess();

        $this->assertEquals($holderOfficeAvailable, $votePlace->isHolderOfficeAvailable());
        $this->assertEquals($substituteOfficeAvailable, $votePlace->isSubstitudeOfficeAvailable());
        $this->assertEquals($availableOffices, $votePlace->getAvailableOfficesAsString());
        $this->assertEquals(false, $assessorRequet->isProcessed());
        $this->assertEquals(false, $assessorRequet->getProcessedAt() instanceof \DateTime);
        $this->assertEquals(true, null === $assessorRequet->getVotePlace());
    }

    public function provideUnprocessTestCases(): \Generator
    {
        yield 'After unprocessing an holder to a vote place, we should have both offices available on the vote place' => [
            AssessorOfficeEnum::HOLDER, 1, 1, "Titulaire\nSuppléant",
        ];
        yield 'After unprocessing a substitude to a vote place, we should have both offices available on the vote place' => [
            AssessorOfficeEnum::SUBSTITUTE, 1, 1, "Titulaire\nSuppléant",
        ];
    }
}
