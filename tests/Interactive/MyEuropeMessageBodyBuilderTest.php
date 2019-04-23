<?php

namespace Tests\AppBundle\Interactive;

use AppBundle\Entity\MyEuropeChoice;
use AppBundle\Interactive\MyEuropeMessageBodyBuilder;
use AppBundle\Interactive\MyEuropeProcessor;
use AppBundle\Repository\MyEuropeChoiceRepository;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class MyEuropeMessageBodyBuilderTest extends TestCase
{
    private $repository;

    public function testBuildMessageBody()
    {
        $introductionText = <<<'EOF'
<p>
%friend_first_name%, ces dernières semaines, nous avons beaucoup parlé du projet de loi de finances, le premier du quinquennat d'Emmanuel Macron.
</p>
EOF;

        $conclusionText = <<<'EOF'
<p>
La République En Marche a publié des documents plus précis à ce sujet et je reste pour ma part à ta disposition pour en reparler!
</p>
EOF;

        $commonText = "Comme 80% des Français, tu vas peut-être bénéficier de la supression progressive de ta taxe d'habitation.";

        $this
            ->repository
            ->expects($this->exactly(2))
            ->method('findMailIntroduction')
            ->willReturn($this->createChoice(0, $introductionText))
        ;

        $this
            ->repository
            ->expects($this->exactly(2))
            ->method('findMailConclusion')
            ->willReturn($this->createChoice(0, $conclusionText))
        ;

        $this
            ->repository
            ->expects($this->exactly(2))
            ->method('findMailCommon')
            ->willReturn($this->createChoice(0, $commonText))
        ;

        $this->createBuilder()->buildMessageBody($myEurope = $this->createMyEuropeProcessor());

        $this->assertSame(
            file_get_contents(__DIR__.'/../Fixtures/files/my_europe_mail.html'),
            $myEurope->messageContent
        );

        $this->createBuilder()->buildMessageBody($myEurope = $this->createMyEuropeProcessor());

        $this->assertSame(
            file_get_contents(__DIR__.'/../Fixtures/files/my_europe_mail_with_empty_argument.html'),
            $myEurope->messageContent
        );
    }

    private function createMyEuropeProcessor(): MyEuropeProcessor
    {
        $myEurope = new MyEuropeProcessor();

        $myEurope->friendFirstName = 'Mylène';
        $myEurope->friendAge = 26;
        $myEurope->friendGender = 'female';
        $myEurope->friendEmail = 'mylene@test.com';
        $myEurope->messageSubject = 'Pourquoi le premier budget du quinquennat est juste, équilibré et profite à tous.';
        $myEurope->selfFirstName = 'Sophie';
        $myEurope->selfLastName = 'Dupont';
        $myEurope->selfEmail = 'sophie.dupont@test.com';
        $myEurope->friendCases = [
            $this->createArgumentChoice(2, 'Nous sommes convaincus que le travail doit mieux payer pour tous les actifs.'),
            $this->createArgumentChoice(2, 'La protection des publics fragiles est au cœur de ce projet de loi de finances.'),
        ];
        $myEurope->friendAppreciations = [
            $this->createArgumentChoice(3, 'Une prime pour l’achat d’un véhicule moins polluant est créée.'),
            $this->createArgumentChoice(3, 'Bon à savoir pour ton projet entrepreneurial : dès 2019, les créateurs d\'une microentreprise aurant droit à une "année blanche" sur leurs cotisations sociales.'),
        ];

        return $myEurope;
    }

    private function createArgumentChoice(int $step, string $measure): MyEuropeChoice
    {
        return $this->createChoice($step, $measure);
    }

    private function createChoice(int $step, string $content): MyEuropeChoice
    {
        return new MyEuropeChoice(
            $uuid = Uuid::uuid4(),
            $step,
            $uuid->getLeastSignificantBitsHex(),
            md5($uuid->toString()),
            $content
        );
    }

    private function createBuilder(): MyEuropeMessageBodyBuilder
    {
        return new MyEuropeMessageBodyBuilder(
            new \Twig_Environment(new \Twig_Loader_Filesystem(__DIR__.'/../Fixtures/views')),
            $this->repository
        );
    }

    protected function setUp()
    {
        parent::setUp();

        $this->repository = $this->createMock(MyEuropeChoiceRepository::class);
    }

    protected function tearDown()
    {
        $this->repository = null;

        parent::tearDown();
    }
}
