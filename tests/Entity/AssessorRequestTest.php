<?php

namespace Tests\App\Entity;

use App\Entity\AssessorOfficeEnum;
use App\Entity\AssessorRequest;
use App\Entity\Election\VotePlace;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('assessor')]
class AssessorRequestTest extends TestCase
{
    #[DataProvider('provideProcessTestCases')]
    public function testAssessorRequestProcess(
        string $assessorOffice,
        bool $holderOfficeAvailable,
        bool $substituteOfficeAvailable,
        array $availableOffices
    ) {
        $assessorRequest = new AssessorRequest();
        $assessorRequest->setOffice($assessorOffice);

        $votePlace = new VotePlace();

        $assessorRequest->process($votePlace);

        $this->assertEquals(true, $assessorRequest->isProcessed());
        $this->assertEquals(true, $assessorRequest->getProcessedAt() instanceof \DateTime);
        $this->assertEquals(false, null === $assessorRequest->getVotePlace());
    }

    public static function provideProcessTestCases(): \Generator
    {
        yield 'After processing an holder to a vote place, we should have only one substitute office available on the vote place' => [
            AssessorOfficeEnum::HOLDER, false, true, ['assessor_request.office.substitute.label'],
        ];
        yield 'After processing a substitute to a vote place, we should have only one holder office available on the vote place' => [
            AssessorOfficeEnum::SUBSTITUTE, true, false, ['assessor_request.office.holder.label'],
        ];
    }

    #[DataProvider('provideUnprocessTestCases')]
    public function testAssessorRequestUnprocess(
        string $assessorOffice,
        bool $holderOfficeAvailable,
        bool $substituteOfficeAvailable,
        array $availableOffices
    ) {
        $assessorRequest = new AssessorRequest();
        $assessorRequest->setOffice($assessorOffice);

        $votePlace = new VotePlace();

        $assessorRequest->process($votePlace);
        $assessorRequest->unprocess();

        $this->assertEquals(false, $assessorRequest->isProcessed());
        $this->assertEquals(false, $assessorRequest->getProcessedAt() instanceof \DateTime);
        $this->assertEquals(true, null === $assessorRequest->getVotePlace());
    }

    public static function provideUnprocessTestCases(): \Generator
    {
        yield 'After unprocessing an holder to a vote place, we should have both offices available on the vote place' => [
            AssessorOfficeEnum::HOLDER, true, true, ['assessor_request.office.holder.label', 'assessor_request.office.substitute.label'],
        ];
        yield 'After unprocessing a substitute to a vote place, we should have both offices available on the vote place' => [
            AssessorOfficeEnum::SUBSTITUTE, true, true, ['assessor_request.office.holder.label', 'assessor_request.office.substitute.label'],
        ];
    }
}
