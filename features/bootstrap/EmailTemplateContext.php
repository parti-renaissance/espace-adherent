<?php

use AppBundle\Mailer\Message\MessageRegistry;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Twig\Environment;

class EmailTemplateContext extends RawMinkContext
{
    private $twig;
    private $messageRegistry;

    /**
     * @var string|null
     */
    private $currentTemplate;

    public function __construct(Environment $twig, MessageRegistry $messageRegistry)
    {
        $this->twig = $twig;
        $this->messageRegistry = $messageRegistry;
    }

    /**
     * @When the :messageClass email template is rendered
     */
    public function theMessageTemplateIsRendered(string $messageClass): void
    {
        $templateName = $this->messageRegistry->getTemplateName("AppBundle\Mailer\Message\\$messageClass");

        $this->currentTemplate = $this->twig->render("email/$templateName.html.twig");
    }

    /**
     * @Then the email template should contain the following variables:
     */
    public function iShouldSeeTheFollowingVariables(TableNode $expectedVars): void
    {
        $expectedVars = $expectedVars->getColumn(0);
        $foundVars = $this->findVars();

        $errors = [];
        foreach ($foundVars as $name) {
            if (!\in_array($name, $expectedVars)) {
                $errors[] = "Variable \"$name\" was not expected in the template.";
            }
        }

        foreach ($expectedVars as $name) {
            if (!\in_array($name, $foundVars)) {
                $errors[] = "Variable \"$name\" was not found in the template.";
            }
        }

        if ($errors) {
            throw new \Exception(implode(PHP_EOL, $errors));
        }
    }

    /**
     * @Then the email template should not contain any variable
     */
    public function iShouldNotSeeAnyVariable(): void
    {
        if (!empty($foundVars = $this->findVars())) {
            throw new \Exception(sprintf(
                'No variable expected, but found "%s" instead.',
                implode(', ', $foundVars)
            ));
        }
    }

    private function findVars(): array
    {
        preg_match_all('/@@(?<name>[a-z0-9_]+)@@/', $this->currentTemplate, $matches);

        return array_unique($matches['name']);
    }
}
