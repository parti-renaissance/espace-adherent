<?php

use AppBundle\Repository\EmailRepository;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

class EmailContext extends RawMinkContext
{
    use KernelDictionary;

    /**
     * @var JsonContext
     */
    private $jsonContext;

    /**
     * @var array|null
     */
    private $currentEmailPayload;

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope): void
    {
        $environment = $scope->getEnvironment();

        $this->jsonContext = $environment->getContext(JsonContext::class);
    }

    /**
     * @Given I should have 1 email :emailType for :emailRecipient with payload:
     */
    public function iShouldHaveEmailForWithPayload(string $emailType, $emailRecipient, PyStringNode $json)
    {
        $emailRepository = $this->getEmailRepository();
        $emails = $emailRepository->findRecipientMessages($emailType, $emailRecipient);

        if (1 !== count($emails)) {
            throw new \RuntimeException(sprintf('I found %s email(s) instead of 1', count($emails)));
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
        if (!$this->currentEmailPayload) {
            throw new \RuntimeException('No email was previously read');
        }

        $link = $this->currentEmailPayload['Recipients'][0]['Vars'][$emailVariableName] ?? null;

        if (!$link) {
            throw new \RuntimeException(sprintf(
                'There is no variable or no data called %s. Variables availables are %s.',
                $emailVariableName,
                implode(', ', array_keys($this->currentEmailPayload['Recipients'][0]['Vars'] ?? []))
            ));
        }

        $this->visitPath($link);
    }

    /**
     * @Given I resolved the captcha
     */
    public function iResolvedTheCaptcha()
    {
        $this->getSession()->getPage()->find('css', 'input[name="g-recaptcha-response"]')->setValue('dummy');
    }

    private function getEmailRepository(): EmailRepository
    {
        return $this->getContainer()->get('app.repository.email');
    }
}
