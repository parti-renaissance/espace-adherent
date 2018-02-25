<?php

use AppBundle\Mailer\Message\MessageRegistry;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Coduo\PHPMatcher\PHPMatcher;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Twig\Environment;

class EmailTemplateContext extends RawMinkContext
{
    use KernelDictionary;

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
     * @When the :messageClass template is rendered
     */
    public function theMessageTemplateIsRendered(string $messageClass): void
    {
        $templateName = $this->messageRegistry->getTemplateName("AppBundle\Mailer\Message\\$messageClass");

        $this->currentTemplate = $this->twig->render("email/$templateName.html.twig");
    }

    /**
     * @Then I should see the following variables:
     */
    public function iShouldSeeTheFollowingVariables(TableNode $variables): void
    {
        $variableNames = [];
        foreach ($variables->getRows() as $variable) {
            $variableName = $variable[0];

            if (!in_array($variableName, $variableNames)) {
                $variableNames[] = $variableName;
            }
        }

        preg_match_all(
            '/{{var:(?<variable_names>[^:]+):"(?<default_values>[^"]*)"}}/',
            $this->currentTemplate,
            $matches
        );

        if (count(array_unique($matches['variable_names'])) !== count($variableNames)) {
            dump($matches);
            die;
            throw new \Exception(sprintf(
                'Expecting %s variables in this template, but found %s.',
                count($variableNames),
                count($matches['variable_names'])
            ));
        }

        foreach ($variableNames as $variableName) {
            if (!in_array($variableName, $matches['variable_names'])) {
                throw new \Exception("Expected variable \"$variableName\" not found in the template.");
            }
        }
    }
}
