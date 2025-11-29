<?php

declare(strict_types=1);

namespace Tests\App\Behat\Context;

use App\Repository\Email\EmailLogRepository;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\MinkExtension\Context\RawMinkContext;

class EmailContext extends RawMinkContext
{
    /**
     * @var JsonContext
     */
    private $jsonContext;

    /**
     * @var array|null
     */
    private $currentEmailPayload;

    private $emailRepository;

    public function __construct(EmailLogRepository $emailRepository)
    {
        $this->emailRepository = $emailRepository;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope): void
    {
        $environment = $scope->getEnvironment();

        $this->jsonContext = $environment->getContext(JsonContext::class);
    }

    /**
     * @Given I should have :number email(s)
     */
    public function iShouldHaveMessages(int $number)
    {
        if (($nb = $this->emailRepository->count([])) !== $number) {
            throw new \RuntimeException(\sprintf('I found %d email(s) instead of %d', $nb, $number));
        }
    }

    /**
     * @Given I should have 1 email :emailType for :emailRecipient with payload:
     */
    public function iShouldHaveEmailForWithPayload(string $emailType, $emailRecipient, PyStringNode $json)
    {
        $emails = $this->emailRepository->findRecipientMessages($emailType, $emailRecipient);

        if (1 !== \count($emails)) {
            throw new \RuntimeException(\sprintf('I found %s email(s) instead of 1', \count($emails)));
        }

        $emailPayloadJson = $emails[0]->getRequestPayloadJson();
        $this->currentEmailPayload = json_decode($emailPayloadJson, true);

        $this->jsonContext->assertJson($json->getRaw(), $emailPayloadJson);
    }

    /**
     * @When I click on the email link :emailVariableName
     */
    public function iClickOnTheEmailLink($emailVariableName)
    {
        $this->visitPath($this->getVarFromEmail($emailVariableName));
    }

    /**
     * @Then I fill activation code from email
     */
    public function iFillActivationCodeFromEmail(): void
    {
        $this->getMink()->getSession()->getPage()->fillField('code', $this->getVarFromEmail('code'));
    }

    private function getVarFromEmail(string $name): string
    {
        if (!$this->currentEmailPayload) {
            throw new \RuntimeException('No email was previously read');
        }

        $value = null;

        $vars = array_merge(
            $this->currentEmailPayload['message']['merge_vars'][0]['vars'] ?? [],
            $this->currentEmailPayload['message']['global_merge_vars'] ?? []
        );

        foreach ($vars as $var) {
            if ($name === $var['name']) {
                $value = $var['content'];
                break;
            }
        }

        if (null === $value) {
            throw new \RuntimeException(\sprintf('There is no variable or no data called %s. Variables availables are %s.', $name, implode(', ', array_keys($this->currentEmailPayload['Recipients'][0]['Vars'] ?? []))));
        }

        return $value;
    }
}
