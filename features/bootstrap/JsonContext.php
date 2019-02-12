<?php

use Behat\Gherkin\Node\PyStringNode;
use Behat\Mink\Exception\ExpectationException;
use Behatch\Context\JsonContext as BehatchJsonContext;
use Coduo\PHPMatcher\PHPMatcher;
use Behat\Gherkin\Node\TableNode;

class JsonContext extends BehatchJsonContext
{
    public function theJsonShouldBeEqualTo(PyStringNode $content)
    {
        $this->assertJson($content->getRaw(), $this->getJson());
    }

    public function assertJson(string $expected, string $actual): void
    {
        $expected = preg_replace('/\s(?=([^"]*"[^"]*")*[^"]*$)/', '', $expected);

        $this->match($expected, $actual);
    }

    /**
     * Checks, that given JSON nodes match values
     *
     * @Then the JSON nodes should match:
     */
    public function theJsonNodesShouldMatch(TableNode $nodes)
    {
        foreach ($nodes->getRowsHash() as $node => $text) {
            $this->theJsonNodeShouldMatch($node, $text);
        }
    }

    /**
     * Checks, that given JSON node matches given value
     *
     * @Then the JSON node :node should match :text
     */
    public function theJsonNodeShouldMatch($node, $text)
    {
        $actual = $this->inspector->evaluate($this->getJson(), $node);

        $this->match($text, \is_bool($actual) ? json_encode($actual) : (string) $actual);
    }

    protected function match($expected, $actual)
    {
        if (!PHPMatcher::match($actual, $expected, $error)) {
            throw new ExpectationException($error, $this->getSession());
        }
    }
}
