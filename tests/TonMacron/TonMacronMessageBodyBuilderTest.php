<?php

namespace Tests\App\TonMacron;

use App\Entity\TonMacronChoice;
use App\Repository\TonMacronChoiceRepository;
use App\TonMacron\InvitationProcessor;
use App\TonMacron\TonMacronMessageBodyBuilder;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class TonMacronMessageBodyBuilderTest extends TestCase
{
    private $repository;

    public function testBuildMessageBody()
    {
        $introductionText = <<<'EOF'
<p>
Bonjour %friend_first_name%,
<br/>
Comme tu le sais, les élections présidentielles auront lieu le 23 avril et 7 mai prochains.
</p>
EOF;
        $conclusionText = <<<'EOF'
<p>
Dis-moi ce que tu en penses. Tu trouveras tous les détails de ces propositions ici.
</p>
EOF;

        $this
            ->repository
            ->expects($this->once())
            ->method('findMailIntroduction')
            ->willReturn($this->createChoice(0, $introductionText))
        ;

        $this
            ->repository
            ->expects($this->once())
            ->method('findMailConclusion')
            ->willReturn($this->createChoice(0, $conclusionText))
        ;

        $this->createBuilder()->buildMessageBody($invitation = $this->createInvitationProcessor());

        $this->assertSame(
            file_get_contents(__DIR__.'/../Fixtures/files/ton_macron_mail.html'),
            $invitation->messageContent
        );
    }

    private function createInvitationProcessor(): InvitationProcessor
    {
        $invitation = new InvitationProcessor();

        $invitation->friendFirstName = 'Béatrice';
        $invitation->friendAge = 32;
        $invitation->friendGender = 'female';
        $invitation->friendEmail = 'beatrice123@domain.tld';
        $invitation->messageSubject = 'Toujours envie de voter blanc ?';
        $invitation->selfFirstName = 'Marie';
        $invitation->selfLastName = 'Dupont';
        $invitation->selfEmail = 'marie.dupont@gmail.tld';
        $invitation->friendPosition = $this->createArgumentChoice(1, 'Tous les 5 ans, en cas de démission, tu auras le droit de bénéficier du chômage.');
        $invitation->friendProject = $this->createArgumentChoice(2, 'Si tu veux investir dans une PME, tu ne seras pas taxé.');
        $invitation->friendInterests = [
            $this->createArgumentChoice(3, "Il lancera un grand plan de transformation agricole de 5 milliards d'euros."),
            $this->createArgumentChoice(3, 'Il créera un « Pass Culture ».'),
        ];
        $invitation->selfReasons = [
            $this->createArgumentChoice(4, 'Emmanuel Macron est différent des responsables politiques.'),
        ];

        return $invitation;
    }

    private function createArgumentChoice(int $step, string $measure): TonMacronChoice
    {
        return $this->createChoice($step, $measure);
    }

    private function createChoice(int $step, string $content): TonMacronChoice
    {
        return new TonMacronChoice(
            $uuid = Uuid::uuid4(),
            $step,
            $uuid->getLeastSignificantBitsHex(),
            md5($uuid->toString()),
            $content
        );
    }

    private function createBuilder(): TonMacronMessageBodyBuilder
    {
        return new TonMacronMessageBodyBuilder(
            new \Twig_Environment(new \Twig_Loader_Filesystem(__DIR__.'/../Fixtures/views')),
            $this->repository
        );
    }

    protected function setUp()
    {
        parent::setUp();

        $this->repository = $this->createMock(TonMacronChoiceRepository::class);
    }

    protected function tearDown()
    {
        $this->repository = null;

        parent::tearDown();
    }
}
