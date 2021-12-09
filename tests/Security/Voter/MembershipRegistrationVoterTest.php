<?php

namespace Tests\App\Security\Voter;

use App\Membership\MembershipRegistrationProcess;
use App\Security\Voter\MembershipRegistrationVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class MembershipRegistrationVoterTest extends TestCase
{
    private $token;

    protected function setUp(): void
    {
        $this->token = $this->createMock(TokenInterface::class);
    }

    protected function tearDown(): void
    {
        $this->token = null;
    }

    /**
     * @dataProvider voterProvider
     */
    public function testVoter(bool $isStarted, array $attributes, int $expected)
    {
        $this->assertSame(
            $expected,
            ($this->createVoter($isStarted))->vote($this->token, null, $attributes)
        );
    }

    public function voterProvider(): iterable
    {
        yield [true, [MembershipRegistrationVoter::REGISTRATION_IN_PROGRESS], VoterInterface::ACCESS_GRANTED];
        yield [false, [MembershipRegistrationVoter::REGISTRATION_IN_PROGRESS], VoterInterface::ACCESS_DENIED];
        yield [false, ['FOO_BAR'], VoterInterface::ACCESS_ABSTAIN];
    }

    public function createMembershipRegistrationProcessMock(bool $isStarted): MembershipRegistrationProcess
    {
        return $this->createConfiguredMock(MembershipRegistrationProcess::class, [
            'isStarted' => $isStarted,
        ]);
    }

    public function createVoter(string $isStarted): MembershipRegistrationVoter
    {
        return new MembershipRegistrationVoter($this->createMembershipRegistrationProcessMock($isStarted));
    }
}
