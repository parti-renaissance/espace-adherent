<?php

namespace Tests\AppBundle\Interactive;

use AppBundle\Entity\PurchasingPowerChoice;
use AppBundle\Repository\PurchasingPowerChoiceRepository;
use AppBundle\Interactive\PurchasingPowerProcessor;
use AppBundle\Interactive\PurchasingPowerMessageBodyBuilder;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class PurchasingPowerMessageBodyBuilderTest extends TestCase
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
            ->expects($this->once())
            ->method('findMailIntroduction')
            ->willReturn($this->createChoice(0, $introductionText));

        $this
            ->repository
            ->expects($this->once())
            ->method('findMailConclusion')
            ->willReturn($this->createChoice(0, $conclusionText));

        $this
            ->repository
            ->expects($this->once())
            ->method('findMailCommon')
            ->willReturn($this->createChoice(0, $commonText));

        $this->createBuilder()->buildMessageBody($purchasingPower = $this->createPurchasingPowerProcessor());

        $this->assertSame(
            file_get_contents(__DIR__.'/../Fixtures/files/purchasing_power_mail.html'),
            $purchasingPower->messageContent
        );
    }

    private function createPurchasingPowerProcessor(): PurchasingPowerProcessor
    {
        $purchasingPower = new PurchasingPowerProcessor();

        $purchasingPower->friendFirstName = 'Mylène';
        $purchasingPower->friendAge = 26;
        $purchasingPower->friendGender = 'female';
        $purchasingPower->friendEmail = 'mylene@test.com';
        $purchasingPower->messageSubject = 'Pourquoi le premier budget du quinquennat est juste, équilibré et profite à tous.';
        $purchasingPower->selfFirstName = 'Sophie';
        $purchasingPower->selfLastName = 'Dupont';
        $purchasingPower->selfEmail = 'sophie.dupont@test.com';
        $purchasingPower->friendPosition = $this->createArgumentChoice(1, 'Tu n’as pas à t\'inquiéter de la hausse de la CSG, qui permet de redonner du pouvoir d\'achat aux salariés et indépendants du secteur privé.');
        $purchasingPower->friendCases = [
            $this->createArgumentChoice(2, 'Nous sommes convaincus que le travail doit mieux payer pour tous les actifs.'),
            $this->createArgumentChoice(2, 'La protection des publics fragiles est au cœur de ce projet de loi de finances.'),
        ];
        $purchasingPower->friendAppreciations = [
            $this->createArgumentChoice(3, 'Une prime pour l’achat d’un véhicule moins polluant est créée.'),
            $this->createArgumentChoice(3, 'Bon à savoir pour ton projet entrepreneurial : dès 2019, les créateurs d\'une microentreprise aurant droit à une "année blanche" sur leurs cotisations sociales.'),
        ];

        return $purchasingPower;
    }

    private function createArgumentChoice(int $step, string $measure): PurchasingPowerChoice
    {
        return $this->createChoice($step, $measure);
    }

    private function createChoice(int $step, string $content): PurchasingPowerChoice
    {
        return new PurchasingPowerChoice(
            $uuid = Uuid::uuid4(),
            $step,
            $uuid->getLeastSignificantBitsHex(),
            md5($uuid->toString()),
            $content
        );
    }

    private function createBuilder(): PurchasingPowerMessageBodyBuilder
    {
        return new PurchasingPowerMessageBodyBuilder(
            new \Twig_Environment(new \Twig_Loader_Filesystem(__DIR__.'/../Fixtures/views')),
            $this->repository
        );
    }

    protected function setUp()
    {
        parent::setUp();

        $this->repository = $this->createMock(PurchasingPowerChoiceRepository::class);
    }

    protected function tearDown()
    {
        $this->repository = null;

        parent::tearDown();
    }
}
